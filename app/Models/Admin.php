<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable([
    'username',
    'password_hash',
    'name',
    'role',
])]
#[Hidden(['password_hash'])]
class Admin extends Authenticatable
{
    protected $table = 'admins';

    public $timestamps = false;

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'created_at' => 'datetime',
        ];
    }

    public function coachNotes(): HasMany
    {
        return $this->hasMany(CoachNote::class, 'coach_id');
    }

    public function coachMessages(): HasMany
    {
        return $this->hasMany(CoachMessage::class, 'coach_id');
    }

    public function assignedPlans(): HasMany
    {
        return $this->hasMany(AssignedPlan::class, 'assigned_by');
    }

    public function coachProfile(): HasOne
    {
        return $this->hasOne(CoachProfile::class, 'admin_id');
    }
}
