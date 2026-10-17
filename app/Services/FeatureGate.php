<?php

namespace App\Services;

use App\Exceptions\PlanLimitExceeded;
use App\Models\Team;

class FeatureGate
{
    /**
     * @return array{members: array{used: int, pending: int, limit: int|null}, projects: array{used: int, limit: int|null}}
     */
    public function snapshot(Team $team): array
    {
        $plan = $team->currentPlan();

        return [
            'members' => [
                'used' => $team->members()->count(),
                'pending' => $team->invitations()->pending()->count(),
                'limit' => $plan->max_members,
            ],
            'projects' => [
                'used' => $team->projects()->count(),
                'limit' => $plan->max_projects,
            ],
        ];
    }

    public function assertCanInvite(Team $team): void
    {
        $limit = $team->currentPlan()->max_members;

        if ($limit === null) {
            return;
        }

        $reserved = $team->members()->count() + $team->invitations()->pending()->count();

        if ($reserved >= $limit) {
            throw PlanLimitExceeded::forLimit('members', $limit);
        }
    }

    public function assertCanAddMember(Team $team): void
    {
        $limit = $team->currentPlan()->max_members;

        if ($limit === null) {
            return;
        }

        if ($team->members()->count() >= $limit) {
            throw PlanLimitExceeded::forLimit('members', $limit);
        }
    }

    public function assertCanCreateProject(Team $team): void
    {
        $limit = $team->currentPlan()->max_projects;

        if ($limit === null) {
            return;
        }

        if ($team->projects()->count() >= $limit) {
            throw PlanLimitExceeded::forLimit('projects', $limit);
        }
    }
}
