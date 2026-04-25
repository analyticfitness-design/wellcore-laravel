<?php

namespace App\Http\View\Composers;

use App\Services\Client\ClientPlanPhaseService;
use Illuminate\View\View;

final class ClientLayoutComposer
{
    public function __construct(private ClientPlanPhaseService $phaseService) {}

    public function compose(View $view): void
    {
        $client = auth('wellcore')->user();
        $view->with('planPhaseText', $client ? $this->phaseService->topbarLabel($client->id) : null);
    }
}
