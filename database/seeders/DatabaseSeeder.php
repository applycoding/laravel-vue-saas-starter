<?php

namespace Database\Seeders;

use App\Enums\TeamRole;
use App\Models\User;
use App\Services\ProjectService;
use App\Services\TeamService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlanSeeder::class);

        $owner = User::factory()->create([
            'name' => 'Demo Owner',
            'email' => 'demo@example.com',
        ]);

        $team = app(TeamService::class)->create($owner, 'Northwind Studio');

        $member = User::factory()->create([
            'name' => 'Demo Member',
            'email' => 'member@example.com',
        ]);

        $team->members()->attach($member->id, [
            'role' => TeamRole::Member->value,
        ]);
        $member->forceFill(['current_team_id' => $team->id])->save();

        app(ProjectService::class)->create(
            $team,
            $owner,
            'Onboarding checklist',
            'A sample project so the workspace is not empty.',
        );
    }
};
