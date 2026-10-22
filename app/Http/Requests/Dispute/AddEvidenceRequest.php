<?php

namespace App\Http\Requests\Dispute;

use Illuminate\Foundation\Http\FormRequest;

class AddEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dispute = $this->route('dispute');
        $user = $this->user();

        // Only parties to the dispute can add evidence
        return $dispute->complainant_id === $user->id || $dispute->respondent_id === $user->id;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:document,screenshot,communication,transaction_record,contract,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'nullable|string',
            'file_url' => 'nullable|url',
            'mime_type' => 'nullable|string',
            'file_size' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ];
    }
}
