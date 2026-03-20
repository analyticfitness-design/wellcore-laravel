<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'category',
    'content_type',
    'audience',
    'plan_access',
    'thumbnail_url',
    'content_url',
    'body_html',
    'description',
    'sort_order',
    'active',
])]
class AcademyContent extends Model
{
    protected $table = 'academy_content';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
