<?php

namespace App\Domains\Development\Models;

use App\Models\DevelopmentProject;
use App\Models\DevelopmentUnit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevelopmentBuilding extends Model
{
    use HasUuids;

    protected $fillable = [
        'development_project_id',
        'name',
        'total_floors',
        'status',
        'progress_percentage',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(DevelopmentProject::class, 'development_project_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(DevelopmentUnit::class);
    }
}
