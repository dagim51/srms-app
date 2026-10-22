<?php

namespace App\Domains\Rental\Models;

use App\Models\Lease;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RentSchedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'lease_id',
        'due_date',
        'amount',
        'status',
        'payment_id',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}
