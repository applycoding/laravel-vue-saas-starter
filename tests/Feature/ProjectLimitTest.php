<?php

use App\Models\Project;
use App\Models\User;

test('the starter plan blocks a fourth project', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');

    foreach (['One', 'Two', 'Three'] as $name) {
        $this->actingAs($owner)->post(route('projects.store'), [
            'name' => $name,
            'description' => null,
        ])->assertRedirect();
    }

    $this->actingAs($owner)
        ->from(route('projects.index'))
        ->post(route('projects.store'), ['name' => 'Four'])
        ->assertRedirect(route('projects.index'))
        ->assertSessionHas('error');

    expect(Project::query()->where('team_id', $team->id)->count())->toBe(3);
});
