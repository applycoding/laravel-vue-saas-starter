<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;

class ProjectService
{
    public function __construct(
        private readonly FeatureGate $features,
        private readonly ActivityLogger $activity,
    ) {}

    public function create(Team $team, User $user, string $name, ?string $description): Project
    {
        $this->features->assertCanCreateProject($team);

        $project = $team->projects()->create([
            'created_by' => $user->id,
            'name' => $name,
            'description' => $description,
        ]);

        $this->activity->log(
            $team,
            $user,
            'project.created',
            "Created project {$project->name}.",
            $project,
        );

        return $project;
    }

    public function delete(Team $team, User $user, Project $project): void
    {
        $name = $project->name;
        $project->delete();

        $this->activity->log(
            $team,
            $user,
            'project.deleted',
            "Deleted project {$name}.",
        );
    }
}
