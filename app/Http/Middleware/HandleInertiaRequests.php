<?php

namespace App\Http\Middleware;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Services\FeatureGate;
use App\Support\CurrentTeamResolver;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $team = $user ? app(CurrentTeamResolver::class)->resolve($user) : null;
        $plan = $team?->currentPlan();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                ] : null,
            ],
            'currentTeam' => $team === null ? null : [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'role' => $user->roleOn($team)?->value,
                'plan' => [
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'price_cents' => $plan->price_cents,
                    'max_members' => $plan->max_members,
                    'max_projects' => $plan->max_projects,
                ],
                'usage' => app(FeatureGate::class)->snapshot($team),
            ],
            'teams' => $user === null ? [] : $user->teams()->orderBy('teams.name')->get()->map(function (Team $membership) use ($user) {
                $role = $membership->pivot->role;

                return [
                    'id' => $membership->id,
                    'name' => $membership->name,
                    'role' => $role instanceof TeamRole ? $role->value : $role,
                    'current' => $membership->id === $user->current_team_id,
                ];
            })->all(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'plainTextToken' => fn () => $request->session()->get('plainTextToken'),
            ],
        ];
    }
}
