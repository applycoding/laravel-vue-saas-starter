<?php

namespace App\Support;

use App\Models\Team;
use App\Models\User;

class CurrentTeamResolver
{
    public function resolve(User $user): ?Team
    {
        $current = $user->currentTeam;

        if ($current !== null && $user->belongsToTeam($current)) {
            return $current;
        }

        $fallback = $user->teams()->first();

        if ($fallback === null) {
            if ($user->current_team_id !== null) {
                $user->forceFill(['current_team_id' => null])->save();
            }

            return null;
        }

        $user->forceFill(['current_team_id' => $fallback->id])->save();

        return $fallback;
    }
}
