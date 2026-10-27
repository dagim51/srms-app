<?php

namespace App\Domains\Engagement\Http\Controllers\Web;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Message;
use App\Models\UserFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageWebController extends Controller
{
    /**
     * Resolve the currently authenticated user.
     */
    protected function getActiveUser()
    {
        return Auth::user();
    }

    public function index()
    {
        $user = $this->getActiveUser();

        // Get conversations where the user is a participant
        $conversations = Conversation::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['participants.user', 'property', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->latest('updated_at')
            ->get();

        return view('dashboard.messages.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $user = $this->getActiveUser();

        // Ensure user is a participant
        if (! $conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        // Mark as read
        $participant = $conversation->participants()->where('user_id', $user->id)->first();
        if ($participant) {
            $participant->update(['last_read_at' => now()]);
        }

        $conversation->load(['messages.sender', 'messages.attachments', 'participants.user', 'property']);

        return view('dashboard.messages.show', compact('conversation'));
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $user = $this->getActiveUser();

        if (! $conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $request->validate([
            'body' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240|mimes:jpeg,png,jpg,pdf,doc,docx,webp',
        ]);

        if (empty($request->body) && ! $request->hasFile('attachment')) {
            return back()->withErrors(['body' => 'Message body or attachment is required.']);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->body ?? '',
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments/messages', 'public');
            $message->attachments()->create([
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_name' => $file->getClientOriginalName(),
            ]);
        }

        $conversation->touch(); // Update updated_at for sorting

        broadcast(new MessageSent($message->load('attachments', 'sender')))->toOthers();

        return back()->with('success', 'Message sent.');
    }

    public function reportMessage(Request $request, Message $message)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'reason' => 'required|string',
            'description' => 'nullable|string',
        ]);

        UserFlag::create([
            'user_id' => $message->sender_id,
            'reporter_id' => $user->id,
            'reason' => $request->reason,
            'description' => $request->description ?? 'Reported offensive in-message content.',
            'severity' => 'medium',
            'status' => 'pending',
            'context_type' => Message::class,
            'context_id' => $message->id,
            'reported_at' => now(),
        ]);

        return back()->with('success', 'Message reported to moderation team.');
    }

    public function storeInquiry(Request $request)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'message' => 'required|string',
        ]);

        $listing = Listing::with('property')->findOrFail($request->listing_id);

        $inquiry = Inquiry::create([
            'requester_id' => $user->id,
            'seller_agent_id' => $listing->owner_actor_id,
            'listing_id' => $listing->id,
            'property_id' => $listing->property_id,
            'status' => 'pending',
            'message' => $request->message,
        ]);

        // Automatically create a conversation
        $conversation = Conversation::create([
            'subject' => 'Inquiry: '.($listing->property->title ?? 'Property'),
            'listing_id' => $listing->id,
            'property_id' => $listing->property_id,
            'inquiry_id' => $inquiry->id,
        ]);

        // Add requester
        $conversation->participants()->create([
            'user_id' => $user->id,
            'last_read_at' => now(),
        ]);

        // Add seller agent
        if ($listing->owner_actor_id) {
            $conversation->participants()->create([
                'user_id' => $listing->owner_actor_id,
            ]);
        }

        // Create the initial message
        $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->message,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'redirect' => route('dashboard.messages.show', $conversation)]);
        }

        return redirect()->route('dashboard.messages.show', $conversation)->with('success', 'Inquiry sent and conversation started.');
    }
}
