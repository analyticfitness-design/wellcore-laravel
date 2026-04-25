<?php

namespace App\Http\Controllers;

use App\Enums\CoachInvitationStatus;
use App\Services\CoachInvitationService;

class CoachInvitationPublicController extends Controller
{
    public function __construct(
        private CoachInvitationService $service,
    ) {}

    public function resolve(string $code)
    {
        $invitation = $this->service->resolveByCode($code);

        if (! $invitation) {
            abort(404);
        }

        if ($invitation->status === CoachInvitationStatus::Cancelled) {
            return response()->view('coach.invitation-cancelled', ['invitation' => $invitation]);
        }

        if ($invitation->status === CoachInvitationStatus::Paid) {
            return response()->view('coach.invitation-already-paid', ['invitation' => $invitation]);
        }

        if ($invitation->isExpired()) {
            return response()->view('coach.invitation-expired', ['invitation' => $invitation]);
        }

        $url = $this->service->trackClickAndGetUrl($invitation);

        return redirect()->away($url);
    }

    public function pixel(string $code)
    {
        $invitation = $this->service->resolveByCode($code);

        if ($invitation) {
            $this->service->trackOpen($invitation);
        }

        return response(
            base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'),
            200
        )
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache');
    }
}
