<?php

namespace App\Domains\Transaction\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'body_content',
        'status',
    ];
}
