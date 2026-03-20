<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'chat_message_id',
    'user_type',
    'client_id',
    'admin_id',
    'emoji',
])]
class ChatMessageReaction extends Model
{
    protected $table = 'chat_message_reactions';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'user_type' => UserType::class,
            'created_at' => 'datetime',
        ];
    }

    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(CoachMessage::class, 'chat_message_id');
    }
}
