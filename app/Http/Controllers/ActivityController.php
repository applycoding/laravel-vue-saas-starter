<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCurrentTeam;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    use ResolvesCurrentTeam;

    public function index(Request $request): Response
    {
        $team = $this->currentTeam($request);

        $activity = ActivityLog::query()
            ->where('team_id', $team->id)
            ->with('user:id,name')
                ->orderByDesc('id')
                ->paginate(20)
            ->withQueryString()
            ->through(fn (ActivityLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user?->name,
                'created_at' => $log->created_at?->toIso8601String(),
            ]);

        return Inertia::render('Activity/Index', [
            'activity' => $activity,
        ]);
    }
}
