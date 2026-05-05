<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachNotificationPreference extends Model
{
    protected $table = 'coach_notification_preferences';

    protected $primaryKey = 'coach_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'coach_id',
        'notify_pr_broken',
        'notify_streak_milestone',
        'notify_post_created',
        'notify_comment_on_my_reply',
        'notify_at_risk_client',
        'notify_official_post_engagement',
        'notify_admin_broadcast',
        'push_enabled',
        'in_app_enabled',
    ];

    protected function casts(): array
    {
        return [
            'notify_pr_broken' => 'boolean',
            'notify_streak_milestone' => 'boolean',
            'notify_post_created' => 'boolean',
            'notify_comment_on_my_reply' => 'boolean',
            'notify_at_risk_client' => 'boolean',
            'notify_official_post_engagement' => 'boolean',
            'notify_admin_broadcast' => 'boolean',
            'push_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }

    public function coach()
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    /**
     * Returns prefs for a coach, creating with defaults if none exist.
     */
    public static function forCoach(int $coachId): self
    {
        return static::firstOrCreate(['coach_id' => $coachId]);
    }
}
