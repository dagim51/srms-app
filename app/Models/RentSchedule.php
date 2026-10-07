<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RentSchedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'lease_id',
        'due_date',
        'amount',
        'status',
        'payment_id'
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}
