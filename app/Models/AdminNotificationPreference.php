<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotificationPreference extends Model
{
    protected $table = 'admin_notification_preferences';

    protected $primaryKey = 'admin_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'notify_post_reported',
        'notify_coach_no_activity_7d',
        'notify_thread_conflict',
        'notify_broadcast_sent',
        'notify_client_spam',
        'push_enabled',
        'in_app_enabled',
    ];

    protected function casts(): array
    {
        return [
            'notify_post_reported' => 'boolean',
            'notify_coach_no_activity_7d' => 'boolean',
            'notify_thread_conflict' => 'boolean',
            'notify_broadcast_sent' => 'boolean',
            'notify_client_spam' => 'boolean',
            'push_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public static function forAdmin(int $adminId): self
    {
        return static::firstOrCreate(['admin_id' => $adminId]);
    }
}
