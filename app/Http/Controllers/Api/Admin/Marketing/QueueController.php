<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Marketing;

use App\Enums\Marketing\DropStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Marketing\QueueRowResource;
use App\Models\Admin;
use App\Models\CoachContentDrop;
use Illuminate\Http\Request;

final class QueueController extends Controller
{
    public function index(Request $request): array
    {
        abort_unless(
            in_array($request->user()->role, [UserRole::Admin, UserRole::Superadmin], strict: true),
            403
        );

        $query = CoachContentDrop::with('coach')
            ->orderByDesc('iso_year')
            ->orderByDesc('iso_week');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($coachId = $request->query('coach_id')) {
            $query->where('coach_id', $coachId);
        }

        if ($year = $request->query('iso_year')) {
            $query->where('iso_year', $year);
        }

        if ($week = $request->query('iso_week')) {
            $query->where('iso_week', $week);
        }

        $rows = $query->paginate(50);

        $monday = now()->startOfWeek();
        $coachesWithoutDropThisWeek = Admin::where('role', UserRole::Coach)
            ->whereNotIn('id', function ($q) use ($monday) {
                $q->select('coach_id')->from('coach_content_drops')
                    ->where('iso_year', (int) $monday->isoFormat('GGGG'))
                    ->where('iso_week', (int) $monday->isoFormat('W'));
            })
            ->count();

        return [
            'data' => QueueRowResource::collection($rows),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'total' => $rows->total(),
                'pending_review_count' => CoachContentDrop::where('status', DropStatus::InReview)->count(),
                'coaches_without_drop_this_week' => $coachesWithoutDropThisWeek,
            ],
        ];
    }
}
