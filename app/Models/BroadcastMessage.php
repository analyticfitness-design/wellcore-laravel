<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadcastMessage extends Model
{
    use HasFactory;

    protected $table = 'broadcast_messages';

    public $timestamps = false;

    protected $fillable = [
        'sender_type',
        'sender_id',
        'audience_type',
        'segment_filter',
        'subject',
        'body',
        'push_enabled',
        'recipients_count',
        'delivered_count',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'segment_filter' => 'array',
            'push_enabled' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'sender_id');
    }
}
