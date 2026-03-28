<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'session_id',
    'visitor_id',
    'client_id',
    'inscription_id',
    'payment_id',
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_content',
    'utm_term',
    'landing_page',
    'referrer',
    'ip_address',
    'user_agent',
    'country',
    'device_type',
    'converted_at',
    'conversion_type',
])]
class PageVisit extends Model
{
    protected $table = 'page_visits';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    /**
     * Filter visits from a specific campaign.
     */
    public function scopeFromCampaign(Builder $query, string $campaign): Builder
    {
        return $query->where('utm_campaign', $campaign);
    }

    /**
     * Filter only visits that resulted in a conversion.
     */
    public function scopeConverted(Builder $query): Builder
    {
        return $query->whereNotNull('converted_at');
    }

    /**
     * Filter visits that have any UTM parameter set.
     */
    public function scopeWithUtm(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNotNull('utm_source')
                ->orWhereNotNull('utm_medium')
                ->orWhereNotNull('utm_campaign')
                ->orWhereNotNull('utm_content')
                ->orWhereNotNull('utm_term');
        });
    }
}
