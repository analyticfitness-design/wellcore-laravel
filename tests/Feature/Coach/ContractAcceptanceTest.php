<?php

namespace Tests\Feature\Coach;

use App\Models\Admin;
use App\Models\AuthToken;
use App\Services\CoachContractService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ContractAcceptanceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_current_version_reads_from_config(): void
    {
        config(['wellcore.coach_contract.version' => '1.0']);

        $service = app(CoachContractService::class);

        $this->assertSame('1.0', $service->getCurrentVersion());
    }

    public function test_has_accepted_current_version_returns_false_when_no_row(): void
    {
        config(['wellcore.coach_contract.version' => '1.0']);

        $coach = Admin::factory()->coach()->create();

        $service = app(CoachContractService::class);

        $this->assertFalse($service->hasAcceptedCurrentVersion($coach->id));
    }

    public function test_record_acceptance_persists_evidence(): void
    {
        config(['wellcore.coach_contract.version' => '1.0']);

        $coach = Admin::factory()->coach()->create();

        $request = \Illuminate\Http\Request::create('/api/v/coach/contract/accept', 'POST', [], [], [], [
            'REMOTE_ADDR'     => '203.0.113.42',
            'HTTP_USER_AGENT' => 'PHPUnit/Test (Plan)',
        ]);

        $service = app(CoachContractService::class);

        $row = $service->recordAcceptance($coach->id, $request, true);

        $this->assertSame('accepted', $row->status);
        $this->assertSame('203.0.113.42', $row->ip_address);
        $this->assertSame('PHPUnit/Test (Plan)', $row->user_agent);
        $this->assertTrue($row->scroll_completed);
        $this->assertNotEmpty($row->content_hash);
        $this->assertSame(64, strlen($row->content_hash));
        $this->assertTrue($service->hasAcceptedCurrentVersion($coach->id));
    }

    public function test_record_decline_deactivates_coach_and_revokes_tokens(): void
    {
        config(['wellcore.coach_contract.version' => '1.0']);

        $coach = Admin::factory()->coach()->create(['active' => true]);

        AuthToken::create([
            'user_id'   => $coach->id,
            'user_type' => 'admin',
            'token'     => str_repeat('b', 64),
            'expires_at' => now()->addDays(30),
        ]);

        $request = \Illuminate\Http\Request::create('/api/v/coach/contract/decline', 'POST', [], [], [], [
            'REMOTE_ADDR'     => '198.51.100.7',
            'HTTP_USER_AGENT' => 'PHPUnit/Decline',
        ]);

        $service = app(CoachContractService::class);
        $row = $service->recordDecline($coach->id, $request);

        $this->assertSame('declined', $row->status);

        $coachAfter = \Illuminate\Support\Facades\DB::table('admins')->where('id', $coach->id)->first();
        $this->assertSame(0, (int) $coachAfter->active);

        $tokensLeft = \Illuminate\Support\Facades\DB::table('auth_tokens')
            ->where('user_id', $coach->id)
            ->where('user_type', 'admin')
            ->count();
        $this->assertSame(0, $tokensLeft);
    }

    public function test_version_bump_forces_re_acceptance(): void
    {
        $coach = Admin::factory()->coach()->create();

        config(['wellcore.coach_contract.version' => '1.0']);
        $service = app(CoachContractService::class);
        $service->recordAcceptance($coach->id, \Illuminate\Http\Request::create('/'), true);
        $this->assertTrue($service->hasAcceptedCurrentVersion($coach->id));

        config(['wellcore.coach_contract.version' => '1.1']);

        // hasAcceptedCurrentVersion checks for v1.1 row (doesn't exist)
        $this->assertFalse($service->hasAcceptedCurrentVersion($coach->id));
    }
}
