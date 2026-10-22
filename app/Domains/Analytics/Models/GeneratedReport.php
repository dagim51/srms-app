<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GeneratedReport extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_template_id',
        'user_id',
        'status',
        'file_path',
        'format',
    ];

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
