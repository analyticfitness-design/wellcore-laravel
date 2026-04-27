<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPulsoReaction extends Model
{
    protected $table = 'client_pulso_reactions';

    public $timestamps = false;

    protected $fillable = ['pulso_id', 'client_id', 'reaction_type'];

    public function pulso(): BelongsTo
    {
        return $this->belongsTo(ClientPulso::class, 'pulso_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
