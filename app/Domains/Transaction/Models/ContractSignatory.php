<?php

namespace App\Domains\Transaction\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ContractSignatory extends Model
{
    use HasUuids;

    protected $fillable = [
        'contract_id',
        'user_id',
        'role',
        'signed_at',
        'signature_ip',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
