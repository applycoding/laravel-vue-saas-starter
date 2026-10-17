<?php

namespace App\Policies;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    public function update(User $user, Team $team): bool
    {
        return $user->hasTeamRole($team, [TeamRole::Owner, TeamRole::Admin]);
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->hasTeamRole($team, TeamRole::Owner);
    }

    public function invite(User $user, Team $team): bool
    {
        return $user->hasTeamRole($team, [TeamRole::Owner, TeamRole::Admin]);
    }

    public function manageBilling(User $user, Team $team): bool
    {
        return $user->hasTeamRole($team, TeamRole::Owner);
    }

    public function removeMember(User $user, Team $team, User $member): bool
    {
        if ($user->is($member)) {
            return false;
        }

        $actor = $user->roleOn($team);
        $target = $member->roleOn($team);

        if ($actor === null || $target === null) {
            return false;
        }

        return match ($actor) {
            TeamRole::Owner => $target !== TeamRole::Owner,
            TeamRole::Admin => $target === TeamRole::Member,
            TeamRole::Member => false,
            default => throw new \LogicException('Unhandled team role ['.$actor->value.'].'),
        };
    }

    public function updateMemberRole(User $user, Team $team, User $member): bool
    {
        if ($user->is($member)) {
            return false;
        }

        $target = $member->roleOn($team);

        return $user->hasTeamRole($team, TeamRole::Owner)
            && $target !== null
            && $target !== TeamRole::Owner;
    }
}
