<?php

namespace App\Domains\Engagement\Http\Controllers\Api\V1;

use App\Domains\Engagement\Http\Controllers\Controller;
use App\Domains\Engagement\Models\Conversation;
use App\Domains\Engagement\Models\Message;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Resources\MessageResource;

class MessageController extends Controller
{
    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();

        $conversation = Conversation::findOrFail($validated['conversation_id']);

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
