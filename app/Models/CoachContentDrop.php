<?php
declare(strict_types=1);
namespace App\Models;

use App\Enums\Marketing\DropStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CoachContentDrop extends Model
{
    use HasFactory;

    protected $table = 'coach_content_drops';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'iso_year'          => 'integer',
            'iso_week'          => 'integer',
            'week_starts_on'    => 'date',
            'status'            => DropStatus::class,
            'content'           => 'array',
            'intake_snapshot'   => 'array',
            'original_content'  => 'array',
            'admin_edits_diff'  => 'array',
            'generated_at'      => 'datetime',
            'reviewed_at'       => 'datetime',
            'approved_at'       => 'datetime',
            'ready_at'          => 'datetime',
            'completed_at'      => 'datetime',
        ];
    }

    public function coach(): BelongsTo { return $this->belongsTo(Admin::class, 'coach_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(Admin::class, 'reviewed_by_id'); }
    public function approver(): BelongsTo { return $this->belongsTo(Admin::class, 'approved_by_id'); }
    public function pieceStates(): HasMany { return $this->hasMany(CoachContentPieceState::class, 'drop_id'); }
}
