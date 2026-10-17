<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Support\CurrentTeamResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CurrentTeamResolver $teams): Response
    {
        $team = $teams->resolve($request->user());

        $activity = $team === null
            ? []
            : ActivityLog::query()
                ->where('team_id', $team->id)
                ->with('user:id,name')
                ->orderByDesc('id')
                ->limit(8)
                ->get()
                ->map(fn (ActivityLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'user' => $log->user?->name,
                    'created_at' => $log->created_at?->toIso8601String(),
                ])
                ->all();

        return Inertia::render('Dashboard', [
            'recentActivity' => $activity,
        ]);
    }
}
