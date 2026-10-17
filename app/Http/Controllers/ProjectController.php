<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCurrentTeam;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use ResolvesCurrentTeam;

    public function index(Request $request): Response
    {
        $team = $this->currentTeam($request);

        $projects = $team->projects()->latest()->get()->map(fn (Project $project) => [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'created_at' => $project->created_at?->toIso8601String(),
        ]);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function store(Request $request, ProjectService $projects): RedirectResponse
    {
        $team = $this->currentTeam($request);
        $this->authorize('create', [Project::class, $team]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $projects->create($team, $request->user(), $data['name'], $data['description'] ?? null);

        return back()->with('success', 'Project created.');
    }

    public function destroy(Request $request, Project $project, ProjectService $projects): RedirectResponse
    {
        $team = $this->currentTeam($request);
        abort_unless($project->team_id === $team->id, 404);
        $this->authorize('delete', $project);

        $projects->delete($team, $request->user(), $project);

        return back()->with('success', 'Project deleted.');
    }
}
