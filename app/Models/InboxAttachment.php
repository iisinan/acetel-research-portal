<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InboxAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'inbox_message_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
    ];

    public function message()
    {
        return $this->belongsTo(InboxMessage::class, 'inbox_message_id');
    }
}
