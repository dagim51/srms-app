<?php

namespace App\Domains\Engagement\Models;

use App\Models\Message;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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
