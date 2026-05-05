<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReport extends Model
{
    protected $table = 'post_reports';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'reporter_id',
        'reason',
        'reason_detail',
        'status',
        'reviewed_by_admin_id',
        'reviewed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function reporter()
    {
        return $this->belongsTo(Client::class, 'reporter_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
