<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ResolveApiTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            return response()->json(['message' => 'This token is not scoped to a team.'], 403);
        }

        $teamId = $this->teamId($token->abilities ?? []);

        if ($teamId === null) {
            return response()->json(['message' => 'This token is not scoped to a team.'], 403);
        }

        $team = Team::query()->find($teamId);

        if ($team === null || $user === null || ! $user->belongsToTeam($team)) {
            return response()->json(['message' => 'You are not a member of this team.'], 403);
        }

        $request->attributes->set('team', $team);

        return $next($request);
    }

    /**
     * @param  list<string>|array<int, string>  $abilities
     */
    private function teamId(array $abilities): ?int
    {
        foreach ($abilities as $ability) {
            if (is_string($ability) && str_starts_with($ability, 'team:') && ctype_digit(substr($ability, 5))) {
                return (int) substr($ability, 5);
            }
        }

        return null;
    }
}
