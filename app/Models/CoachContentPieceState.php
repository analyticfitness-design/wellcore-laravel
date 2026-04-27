<?php
declare(strict_types=1);
namespace App\Models;

use App\Enums\Marketing\PieceState;
use App\Enums\Marketing\PieceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CoachContentPieceState extends Model
{
    use HasFactory;

    protected $table = 'coach_content_piece_states';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'piece_type'       => PieceType::class,
            'state'            => PieceState::class,
            'state_changed_at' => 'datetime',
        ];
    }

    public function drop(): BelongsTo { return $this->belongsTo(CoachContentDrop::class, 'drop_id'); }
    public function coach(): BelongsTo { return $this->belongsTo(Admin::class, 'coach_id'); }
}
