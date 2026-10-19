<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'body' => 'required|string',
        ]);

        $user = auth()->user();

        // Ensure user is participant
        $conversation = Conversation::findOrFail($validated['conversation_id']);

        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();

        if (! $isParticipant) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
        ]);

        // Update sender's last read at
        $conversation->participants()->where('user_id', $user->id)->update([
            'last_read_at' => now(),
        ]);

        $message->load('sender', 'attachments');

        return response()->json(['data' => new MessageResource($message)], 201);
    }

    public function destroy(Message $message)
    {
        $user = auth()->user();

        if ($message->sender_id !== $user->id) {
            abort(403, 'Only the sender can delete the message.');
        }

        $message->delete();

        return response()->noContent();
    }
}
