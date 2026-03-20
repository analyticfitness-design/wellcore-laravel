<?php

namespace App\Models;

use App\Enums\ClientStatus;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable([
    'client_code',
    'name',
    'email',
    'password_hash',
    'plan',
    'status',
    'fecha_inicio',
    'avatar_url',
    'bio',
    'city',
    'birth_date',
    'referral_code',
    'referred_by',
])]
#[Hidden(['password_hash'])]
class Client extends Authenticatable
{
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
}
