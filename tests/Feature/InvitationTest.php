<?php

use App\Enums\TeamRole;
use App\Models\User;

test('an admin can invite someone and that person can accept', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');
    $admin = addTeammate($team, TeamRole::Admin);

    $this->actingAs($admin)
        ->post(route('invitations.store', $team), [
            'email' => 'new.member@example.com',
            'role' => 'member',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $invitee = User::factory()->create(['email' => 'new.member@example.com']);
    $token = $team->invitations()->firstOrFail()->token;

    $this->actingAs($invitee)
        ->post(route('invitations.accept', $token))
        ->assertRedirect(route('teams.index'));

    expect($invitee->fresh()->roleOn($team->fresh()))->toBe(TeamRole::Member);
});

test('a member cannot invite people', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');
    $member = addTeammate($team, TeamRole::Member);

    $this->actingAs($member)
        ->post(route('invitations.store', $team), [
            'email' => 'other@example.com',
            'role' => 'member',
        ])
        ->assertForbidden();
});

test('invites stop when the starter member limit is reserved', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');

    $this->actingAs($owner)->post(route('invitations.store', $team), [
        'email' => 'one@example.com',
        'role' => 'member',
    ])->assertRedirect();

    $this->actingAs($owner)->post(route('invitations.store', $team), [
        'email' => 'two@example.com',
        'role' => 'admin',
    ])->assertRedirect();

    $this->actingAs($owner)
        ->from(route('teams.index'))
        ->post(route('invitations.store', $team), [
            'email' => 'three@example.com',
            'role' => 'member',
        ])
        ->assertRedirect(route('teams.index'))
        ->assertSessionHas('error');

    expect($team->invitations()->count())->toBe(2);
});
