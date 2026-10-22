<?php

namespace App\Domains\Development\Models;

use App\Models\DevelopmentProject;
use App\Models\DevelopmentUnit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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

    public function project()
    {
        return $this->belongsTo(DevelopmentProject::class, 'development_project_id');
    }

    public function units()
    {
        return $this->hasMany(DevelopmentUnit::class);
    }
}
