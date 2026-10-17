<?php

namespace App\Http\Controllers;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function update(Request $request, Team $team, User $member, ActivityLogger $activity): RedirectResponse
    {
        $this->authorize('updateMemberRole', [$team, $member]);

        $data = $request->validate([
            'role' => ['required', Rule::in([TeamRole::Admin->value, TeamRole::Member->value])],
        ]);

        $role = TeamRole::from($data['role']);

        $team->members()->updateExistingPivot($member->id, [
            'role' => $role->value,
        ]);

        $activity->log(
            $team,
            $request->user(),
            'member.role_updated',
            "Set {$member->name}'s role to {$role->label()}.",
            $member,
        );

        return back()->with('success', 'Role updated.');
    }

    public function destroy(Request $request, Team $team, User $member, ActivityLogger $activity): RedirectResponse
    {
        $this->authorize('removeMember', [$team, $member]);

        $team->members()->detach($member->id);

        if ($member->current_team_id === $team->id) {
            $fallback = $member->teams()->first();
            $member->forceFill(['current_team_id' => $fallback?->id])->save();
        }

        $activity->log(
            $team,
            $request->user(),
            'member.removed',
            "Removed {$member->name} from the team.",
            $member,
        );

        return back()->with('success', 'Member removed.');
    }
}
