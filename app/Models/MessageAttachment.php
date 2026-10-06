<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MessageAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'message_id',
        'file_path',
        'file_type',
        'file_name',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
