<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientNotificationPreference extends Model
{
    protected $table = 'client_notification_preferences';

    protected $primaryKey = 'client_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'notify_post_reactions',
        'notify_comments_on_my_post',
        'notify_mentions',
        'notify_coach_messages',
        'notify_coach_announcements',
        'notify_wellcore_announcements',
        'push_enabled',
        'in_app_enabled',
    ];

    protected function casts(): array
    {
        return [
            'notify_post_reactions' => 'boolean',
            'notify_comments_on_my_post' => 'boolean',
            'notify_mentions' => 'boolean',
            'notify_coach_messages' => 'boolean',
            'notify_coach_announcements' => 'boolean',
            'notify_wellcore_announcements' => 'boolean',
            'push_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public static function forClient(int $clientId): self
    {
        return static::firstOrCreate(['client_id' => $clientId]);
    }
}
