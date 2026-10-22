<?php

namespace App\Domains\Rental\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TenantProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'employment_status',
        'annual_income',
        'background_check_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
