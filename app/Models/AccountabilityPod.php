<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'coach_id',
    'name',
    'description',
    'max_members',
    'is_active',
])]
class AccountabilityPod extends Model
{
    protected $table = 'accountability_pods';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(PodMember::class, 'pod_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(PodMessage::class, 'pod_id');
    }
}
