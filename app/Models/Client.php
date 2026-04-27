<?php

namespace App\Models;

use App\Enums\ClientStatus;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable([
    'client_code',
    'name',
    'email',
    'timezone',
    'password_hash',
    'google_id',
    'plan',
    'status',
    'fecha_inicio',
    'avatar_url',
    'bio',
    'city',
    'birth_date',
    'referral_code',
    'referred_by',
    'onboarding_completed',
    'autoshare_workout',
    'autoshare_pr',
    'autoshare_medal',
    'autoshare_weight',
    'autoshare_streak',
])]
#[Hidden(['password_hash'])]
class Client extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'clients';

    public $timestamps = true;

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    protected function casts(): array
    {
        return [
            'plan' => PlanType::class,
            'status' => ClientStatus::class,
            'fecha_inicio' => 'date',
            'birth_date' => 'date',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function xp(): HasOne
    {
        return $this->hasOne(ClientXp::class);
    }

    public function assignedPlans(): HasMany
    {
        return $this->hasMany(AssignedPlan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function coachMessages(): HasMany
    {
        return $this->hasMany(CoachMessage::class);
    }

    public function biometricLogs(): HasMany
    {
        return $this->hasMany(BiometricLog::class);
    }

    public function trainingLogs(): HasMany
    {
        return $this->hasMany(TrainingLog::class);
    }

    public function habitLogs(): HasMany
    {
        return $this->hasMany(HabitLog::class);
    }

    public function challengeParticipations(): HasMany
    {
        return $this->hasMany(ChallengeParticipant::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(WellcoreNotification::class, 'user_id');
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function weightLogs(): HasMany
    {
        return $this->hasMany(WeightLog::class);
    }

    public function progressPhotos(): HasMany
    {
        return $this->hasMany(ProgressPhoto::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function risePrograms(): HasMany
    {
        return $this->hasMany(RiseProgram::class);
    }

    public function shopOrders(): HasMany
    {
        return $this->hasMany(ShopOrder::class);
    }

    public function medals(): BelongsToMany
    {
        return $this->belongsToMany(Medal::class, 'client_medals')
            ->withPivot(['current_progress', 'achieved_at'])
            ->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'follows', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'follows', 'followed_id', 'follower_id')
            ->withTimestamps();
    }

    public function coaches(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'client_coach', 'client_id', 'admin_id')
            ->wherePivot('active', true);
    }

    public function activeCoach(): ?Admin
    {
        // Try new client_coach table first, then legacy coach_id column
        return $this->coaches()->first()
            ?? ($this->coach_id ? Admin::find($this->coach_id) : null);
    }
}
