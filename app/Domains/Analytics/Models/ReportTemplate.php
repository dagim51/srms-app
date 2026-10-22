<?php

namespace App\Domains\Analytics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type',
        'configuration',
    ];

    protected $casts = [
        'configuration' => 'array',
    ];
}
