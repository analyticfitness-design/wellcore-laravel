<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPulsoView extends Model
{
    protected $table = 'client_pulso_views';

    public $timestamps = false;

    protected $fillable = ['pulso_id', 'viewer_id', 'viewed_at'];

    protected $casts = ['viewed_at' => 'datetime'];

    public function pulso(): BelongsTo
    {
        return $this->belongsTo(ClientPulso::class, 'pulso_id');
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'viewer_id');
    }
}
