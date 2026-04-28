<?php

use App\Models\AuditLog;
use App\Traits\Auditable;

it('audit attaches impersonation_log_id when chain in session', function () {
    session(['wc_impersonation_chain' => [
        ['level' => 1, 'log_id' => 999, 'token' => 'abc', 'target_type' => 'admin', 'target_id' => 7, 'target_name' => 'Pedro'],
    ]]);

    $controller = new class { use Auditable; public function call() { $this->audit('test.action'); } };
    $controller->call();

    $row = AuditLog::query()->latest('id')->first();
    expect($row)->not->toBeNull();
    expect($row->diff['impersonation_log_id'] ?? null)->toBe(999);
});

it('audit does not attach id when no chain', function () {
    session()->forget('wc_impersonation_chain');

    $controller = new class { use Auditable; public function call() { $this->audit('test.action'); } };
    $controller->call();

    $row = AuditLog::query()->latest('id')->first();
    expect($row)->not->toBeNull();
    expect((array) ($row->diff ?? []))->not->toHaveKey('impersonation_log_id');
});
