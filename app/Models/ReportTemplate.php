<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type',
        'configuration'
    ];

    protected $casts = [
        'configuration' => 'array'
    ];
}
