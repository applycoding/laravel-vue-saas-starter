<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Team;
use App\Support\CurrentTeamResolver;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

trait ResolvesCurrentTeam
{
    protected function currentTeam(Request $request): Team
    {
        $team = app(CurrentTeamResolver::class)->resolve($request->user());

        if ($team === null) {
            throw new HttpResponseException(
                redirect()->route('teams.index')->with('error', 'Create a team to continue.')
            );
        }

        return $team;
    }
}
