<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'created_by' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
        ];
    }
}
