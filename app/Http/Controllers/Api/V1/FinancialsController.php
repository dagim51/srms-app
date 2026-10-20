<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\Invoice;
use App\Http\Resources\LedgerAccountResource;
use App\Http\Resources\LedgerEntryResource;
use App\Http\Resources\InvoiceResource;

class FinancialsController extends Controller
{
    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:ledger_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Asset,Liability,Equity,Revenue,Expense'
        ]);

        $account = LedgerAccount::create($validated);
        return (new LedgerAccountResource($account))->response()->setStatusCode(201);
    }

    public function recordDoubleEntry(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'entry_date' => 'required|date',
            'lines' => 'required|array|min:2',
            'lines.*.ledger_account_id' => 'required|uuid|exists:ledger_accounts,id',
            'lines.*.type' => 'required|in:debit,credit',
            'lines.*.amount' => 'required|numeric|min:0'
        ]);

        // Validate double-entry logic
        $debits = 0;
        $credits = 0;
        foreach ($validated['lines'] as $line) {
            if ($line['type'] === 'debit') $debits += $line['amount'];
            else $credits += $line['amount'];
        }

        if (abs($debits - $credits) > 0.01) {
            return response()->json(['message' => 'Debits and credits must balance'], 400);
        }

        $entry = LedgerEntry::create([
            'description' => $validated['description'],
            'entry_date' => $validated['entry_date']
        ]);

        foreach ($validated['lines'] as $line) {
            $entry->lines()->create($line);
        }

        return (new LedgerEntryResource($entry->load('lines')))->response()->setStatusCode(201);
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'amount' => 'required|numeric',
            'due_date' => 'required|date'
        ]);

        $invoice = Invoice::create([
            'user_id' => $validated['user_id'],
            'amount' => $validated['amount'],
            'due_date' => $validated['due_date'],
            'status' => 'draft'
        ]);

        return (new InvoiceResource($invoice))->response()->setStatusCode(201);
    }
}
