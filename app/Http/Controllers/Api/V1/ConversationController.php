<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $conversations = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['participants.user'])
        ->get();
        
        return response()->json(['data' => $conversations]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'inquiry_id' => 'nullable|exists:inquiries,id',
            'listing_id' => 'nullable|exists:listings,id',
            'property_id' => 'nullable|exists:properties,id',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:users,id',
        ]);
        
        $conversation = Conversation::create([
            'subject' => $validated['subject'] ?? null,
            'inquiry_id' => $validated['inquiry_id'] ?? null,
            'listing_id' => $validated['listing_id'] ?? null,
            'property_id' => $validated['property_id'] ?? null,
        ]);
        
        $participantIds = collect($validated['participant_ids'])
            ->push(auth()->id())
            ->unique();
            
        foreach ($participantIds as $userId) {
            $conversation->participants()->create([
                'user_id' => $userId,
            ]);
        }
        
        $conversation->load('participants.user');
        
        return response()->json(['data' => $conversation], 201);
    }

    public function show(Conversation $conversation)
    {
        $user = auth()->user();
        
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        
        if (!$isParticipant) {
            abort(403, 'Unauthorized access to conversation.');
        }
        
        $conversation->load(['participants.user', 'messages.sender', 'messages.attachments']);
        
        // Update last_read_at
        $conversation->participants()->where('user_id', $user->id)->update([
            'last_read_at' => now(),
        ]);
        
        return response()->json(['data' => $conversation]);
    }

    public function destroy(Conversation $conversation)
    {
        $user = auth()->user();
        
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        
        if (!$isParticipant) {
            abort(403, 'Unauthorized access to conversation.');
        }
        
        $conversation->delete();
        
        return response()->noContent();
    }
}
