<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SearchAlert extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'criteria',
        'frequency',
        'is_active',
        'last_notified_at',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
        'last_notified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
