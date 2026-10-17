<?php

use App\Models\ActivityLog;
use App\Models\User;

test('creating a project writes an activity row the team can read', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');

    $this->actingAs($owner)->post(route('projects.store'), [
        'name' => 'Launch plan',
    ])->assertRedirect();

    $log = ActivityLog::query()->where('team_id', $team->id)->where('action', 'project.created')->first();

    expect($log)->not->toBeNull()
        ->and($log->description)->toContain('Launch plan');

    $this->actingAs($owner)
        ->get(route('activity.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Activity/Index')
            ->where('activity.data.0.action', 'project.created'));
});
