<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_type',
    'user_id',
    'type',
    'title',
    'body',
    'link',
    'read_at',
])]
class WellcoreNotification extends Model
{
    protected $table = 'notifications';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'user_type' => UserType::class,
            'read_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
