<?php

namespace App\Http\Requests\Message;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversation = Conversation::findOrFail($this->input('conversation_id'));
        $user = $this->user();

        return $conversation->participants()->where('user_id', $user->id)->exists();
    }

    public function rules(): array
    {
        return [
            'conversation_id' => 'required|exists:conversations,id',
            'body' => 'required|string',
        ];
    }
}
