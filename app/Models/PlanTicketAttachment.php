<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PlanTicketAttachment extends Model
{
    protected $table = 'plan_ticket_attachments';

    protected $fillable = [
        'plan_ticket_id',
        'uploaded_by_type',
        'uploaded_by_id',
        'uploaded_by_name',
        'original_name',
        'stored_name',
        'mime',
        'size_bytes',
        'category',
        'disk',
        'path',
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(PlanTicket::class, 'plan_ticket_id');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        return Storage::disk($this->disk ?: 'public')->url($this->path);
    }
}
