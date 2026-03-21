<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'session_id',
        'role',
        'message',
        'page_url',
        'ip_hash',
        'user_agent',
    ];
}
