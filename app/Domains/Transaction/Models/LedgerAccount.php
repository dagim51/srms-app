<?php

namespace App\Domains\Transaction\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LedgerAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
        'type',
    ];
}
