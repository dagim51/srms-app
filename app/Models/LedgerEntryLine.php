<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LedgerEntryLine extends Model
{
    use HasUuids;

    protected $fillable = [
        'ledger_entry_id',
        'ledger_account_id',
        'type',
        'amount'
    ];

    public function entry()
    {
        return $this->belongsTo(LedgerEntry::class, 'ledger_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }
}
