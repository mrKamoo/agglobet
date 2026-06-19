<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessageReaction extends Model
{
    protected $fillable = [
        'chat_message_id',
        'user_id',
        'emoji',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatMessage()
    {
        return $this->belongsTo(ChatMessage::class);
    }
}
