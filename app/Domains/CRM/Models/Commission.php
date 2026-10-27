<?php

namespace App\Domains\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasUuids;

    protected $fillable = [
        'agent_id',
        'deal_id',
        'deal_type',
        'gross_amount',
        'rate_percentage',
        'commission_amount',
        'currency',
        'status',
        'paid_at',
        'notes',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
