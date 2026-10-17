<?php

use App\Models\User;
use App\Services\ProjectService;

test('a sanctum token lists and creates projects for its team only', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');
    $token = $owner->createToken('ci', ['team:'.$team->id])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/projects', ['name' => 'Alpha', 'description' => 'First'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Alpha');

    $this->withToken($token)
        ->getJson('/api/v1/projects')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Alpha');

    $this->withToken($token)
        ->getJson('/api/v1/team')
        ->assertOk()
        ->assertJsonPath('data.slug', $team->slug);

    $other = User::factory()->create();
    $otherTeam = provisionTeam($other, 'Other');
    $secret = app(ProjectService::class)->create($otherTeam, $other, 'Secret', null);

    $this->withToken($token)
        ->getJson('/api/v1/projects/'.$secret->id)
        ->assertNotFound();
});

test('a token without a team ability is rejected', function () {
    $owner = User::factory()->create();
    provisionTeam($owner, 'Acme');
    $token = $owner->createToken('bare')->plainTextToken;

    $this->withToken($token)->getJson('/api/v1/projects')->assertForbidden();
});

test('the api enforces the project limit', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');
    $token = $owner->createToken('ci', ['team:'.$team->id])->plainTextToken;

    foreach (['One', 'Two', 'Three'] as $name) {
        $this->withToken($token)->postJson('/api/v1/projects', ['name' => $name])->assertCreated();
    }

    $this->withToken($token)
        ->postJson('/api/v1/projects', ['name' => 'Four'])
        ->assertStatus(422);
});
