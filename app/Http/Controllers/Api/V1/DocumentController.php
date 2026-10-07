<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentTemplate;
use App\Models\Contract;
use App\Models\ContractSignatory;

class DocumentController extends Controller
{
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'body_content' => 'required|string',
        ]);

        $template = DocumentTemplate::create($validated);
        return response()->json(['data' => $template], 201);
    }

    public function generateContract(Request $request)
    {
        $validated = $request->validate([
            'document_template_id' => 'required|uuid|exists:document_templates,id',
            'title' => 'required|string|max:255',
            'signatories' => 'required|array',
            'signatories.*.user_id' => 'required|uuid|exists:users,id',
            'signatories.*.role' => 'required|string'
        ]);

        $template = DocumentTemplate::findOrFail($validated['document_template_id']);

        $contract = Contract::create([
            'document_template_id' => $template->id,
            'title' => $validated['title'],
            'body_content' => $template->body_content,
            'status' => 'sent'
        ]);

        foreach ($validated['signatories'] as $sig) {
            $contract->signatories()->create([
                'user_id' => $sig['user_id'],
                'role' => $sig['role']
            ]);
        }

        return response()->json(['data' => $contract->load('signatories')], 201);
    }

    public function signContract(Request $request, Contract $contract)
    {
        $signatory = $contract->signatories()->where('user_id', auth()->id())->firstOrFail();

        if ($signatory->signed_at) {
            return response()->json(['message' => 'Already signed'], 400);
        }

        $signatory->update([
            'signed_at' => now(),
            'signature_ip' => $request->ip()
        ]);

        $allSigned = $contract->signatories()->whereNull('signed_at')->count() === 0;

        if ($allSigned) {
            $contract->update(['status' => 'fully_signed']);
        } else {
            $contract->update(['status' => 'partial_signed']);
        }

        return response()->json(['data' => $contract->load('signatories')]);
    }
}
