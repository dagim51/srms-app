<?php

namespace App\Domains\Development\Models;

use App\Models\DevelopmentProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConstructionProgress extends Model
{
    use HasUuids;

    protected $table = 'construction_progress';

    protected $fillable = [
        'project_id',
        'milestone_name',
        'completion_percentage',
        'verified_at',
        'inspector_id',
        'media_urls',
        'notes',
    ];

    protected $casts = [
        'completion_percentage' => 'float',
        'verified_at' => 'datetime',
        'media_urls' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(DevelopmentProject::class, 'project_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
