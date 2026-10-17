<?php

namespace App\Http\Controllers;

use App\Enums\TeamRole;
use App\Http\Controllers\Concerns\ResolvesCurrentTeam;
use App\Models\Team;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\TeamService;
use App\Support\CurrentTeamResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    use ResolvesCurrentTeam;

    public function index(Request $request, CurrentTeamResolver $resolver): Response
    {
        $user = $request->user();
        $team = $resolver->resolve($user);

        $members = [];
        $invitations = [];
        $abilities = [
            'update' => false,
            'delete' => false,
            'invite' => false,
        ];

        if ($team !== null) {
            $abilities = [
                'update' => $user->can('update', $team),
                'delete' => $user->can('delete', $team),
                'invite' => $user->can('invite', $team),
            ];

            $members = $team->members()->orderBy('name')->get()->map(function (User $member) use ($user, $team) {
                $role = $member->roleOn($team);

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $role?->value,
                    'can_remove' => $user->can('removeMember', [$team, $member]),
                    'can_change_role' => $user->can('updateMemberRole', [$team, $member]),
                ];
            })->all();

            $invitations = $team->invitations()->pending()->latest()->get()->map(fn ($invitation) => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role instanceof TeamRole ? $invitation->role->value : $invitation->role,
                'expires_at' => $invitation->expires_at?->toIso8601String(),
                'accept_url' => route('invitations.show', $invitation->token),
            ])->all();
        }

        return Inertia::render('Teams/Index', [
            'members' => $members,
            'invitations' => $invitations,
            'abilities' => $abilities,
        ]);
    }

    public function store(Request $request, TeamService $teams): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $teams->create($request->user(), $data['name']);

        return redirect()->route('teams.index')->with('success', 'Team created.');
    }

    public function update(Request $request, Team $team, ActivityLogger $activity): RedirectResponse
    {
        $this->authorize('update', $team);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $team->update([
            'name' => $data['name'],
            'slug' => $this->slugFor($team, $data['name']),
        ]);

        $activity->log($team, $request->user(), 'team.updated', "Renamed the team to {$team->name}.", $team);

        return back()->with('success', 'Team updated.');
    }

    public function destroy(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('delete', $team);

        $user = $request->user();
        $team->delete();

        $fallback = $user->teams()->first();
        $user->forceFill(['current_team_id' => $fallback?->id])->save();

        return redirect()->route('teams.index')->with('success', 'Team deleted.');
    }

    public function switch(Request $request, Team $team, TeamService $teams): RedirectResponse
    {
        $teams->switch($request->user(), $team);

        return back()->with('success', "Switched to {$team->name}.");
    }

    private function slugFor(Team $team, string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'team';

        if ($team->slug === $base || str_starts_with($team->slug, $base.'-')) {
            $taken = Team::query()->where('slug', $base)->whereKeyNot($team->id)->exists();

            if (! $taken && $team->slug !== $base && ! str_contains($team->slug, '-')) {
                return $base;
            }
        }

        $slug = $base;
        $suffix = 2;

        while (Team::query()->where('slug', $slug)->whereKeyNot($team->id)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
