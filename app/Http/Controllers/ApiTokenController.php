<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCurrentTeam;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    use ResolvesCurrentTeam;

    public function index(Request $request): Response
    {
        $this->currentTeam($request);

        $tokens = $request->user()->tokens()->latest()->get()->map(fn ($token) => [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'created_at' => $token->created_at?->toIso8601String(),
        ]);

        return Inertia::render('ApiTokens/Index', [
            'tokens' => $tokens,
        ]);
    }

    public function store(Request $request, ActivityLogger $activity): RedirectResponse
    {
        $team = $this->currentTeam($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $token = $request->user()->createToken($data['name'], ['team:'.$team->id]);

        $activity->log(
            $team,
            $request->user(),
            'token.created',
            "Created API token {$data['name']}.",
        );

        return back()->with('plainTextToken', $token->plainTextToken);
    }

    public function destroy(Request $request, int $token, ActivityLogger $activity): RedirectResponse
    {
        $team = $this->currentTeam($request);
        $accessToken = $request->user()->tokens()->whereKey($token)->firstOrFail();
        $name = $accessToken->name;
        $accessToken->delete();

        $activity->log($team, $request->user(), 'token.revoked', "Revoked API token {$name}.");

        return back()->with('success', 'Token revoked.');
    }
}
