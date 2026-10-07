<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TenantProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'employment_status',
        'annual_income',
        'background_check_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
