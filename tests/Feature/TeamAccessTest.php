<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;

test('registering creates a starter team owned by the new user', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();
    $team = $user->currentTeam;

    expect($team)->not->toBeNull()
        ->and($user->roleOn($team))->toBe(TeamRole::Owner)
        ->and($team->currentPlan()->slug)->toBe('starter');
});

test('a member cannot rename the team and an admin can', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');
    $admin = addTeammate($team, TeamRole::Admin);
    $member = addTeammate($team, TeamRole::Member);

    $this->actingAs($member)
        ->patch(route('teams.update', $team), ['name' => 'Hijacked'])
        ->assertForbidden();

    $this->actingAs($admin)
        ->patch(route('teams.update', $team), ['name' => 'Acme Studio'])
        ->assertRedirect();

    expect($team->fresh()->name)->toBe('Acme Studio');
});

test('only the owner can delete a team', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');
    $admin = addTeammate($team, TeamRole::Admin);

    $this->actingAs($admin)->delete(route('teams.destroy', $team))->assertForbidden();
    expect(Team::query()->whereKey($team->id)->exists())->toBeTrue();

    $this->actingAs($owner)->delete(route('teams.destroy', $team))->assertRedirect(route('teams.index'));
    expect(Team::query()->whereKey($team->id)->exists())->toBeFalse();
});

test('an admin cannot remove the owner', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');
    $admin = addTeammate($team, TeamRole::Admin);

    $this->actingAs($admin)
        ->delete(route('members.destroy', [$team, $owner]))
        ->assertForbidden();

    expect($owner->fresh()->belongsToTeam($team))->toBeTrue();
});

test('the owner can remove a member', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');
    $member = addTeammate($team, TeamRole::Member);

    $this->actingAs($owner)
        ->delete(route('members.destroy', [$team, $member]))
        ->assertRedirect();

    expect($member->fresh()->belongsToTeam($team))->toBeFalse();
});
