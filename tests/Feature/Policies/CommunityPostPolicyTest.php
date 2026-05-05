<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Policies\CommunityPostPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->policy = new CommunityPostPolicy;
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    $this->admin = Admin::factory()->create(['role' => 'superadmin']);
    $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);
    $this->post = CommunityPost::factory()->create([
        'client_id' => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);
});

it('allows coach to moderate their own clients post', function () {
    expect($this->policy->canModerate($this->coach, $this->post))->toBeTrue();
});

it('rejects coach from moderating another coachs post', function () {
    $otherCoach = Admin::factory()->create(['role' => 'coach']);
    expect($this->policy->canModerate($otherCoach, $this->post))->toBeFalse();
});

it('allows superadmin to moderate any post', function () {
    expect($this->policy->canModerate($this->admin, $this->post))->toBeTrue();
});

it('allows coach to pin their own clients post', function () {
    expect($this->policy->canPin($this->coach, $this->post))->toBeTrue();
});

it('allows coach to make official their own clients post', function () {
    expect($this->policy->canMakeOfficial($this->coach, $this->post))->toBeTrue();
});

it('allows admin to delete any post', function () {
    expect($this->policy->canDelete($this->admin, $this->post))->toBeTrue();
});
