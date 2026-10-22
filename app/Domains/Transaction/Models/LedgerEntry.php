<?php

namespace App\Domains\Transaction\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    use HasUuids;

    protected $fillable = [
        'description',
        'reference_id',
        'entry_date',
    ];

    public function lines()
    {
        return $this->hasMany(LedgerEntryLine::class);
    }
}
