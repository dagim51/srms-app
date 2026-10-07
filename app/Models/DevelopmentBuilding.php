<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DevelopmentBuilding extends Model
{
    use HasUuids;

    protected $fillable = [
        'development_project_id',
        'name',
        'total_floors',
        'status',
        'progress_percentage'
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
