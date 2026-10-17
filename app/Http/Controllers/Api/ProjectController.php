<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $team = $this->team($request);

        $projects = $team->projects()->latest()->get()->map(fn (Project $project) => $this->transform($project));

        return response()->json(['data' => $projects]);
    }

    public function store(Request $request, ProjectService $projects): JsonResponse
    {
        $team = $this->team($request);
        $this->authorize('create', [Project::class, $team]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $project = $projects->create(
            $team,
            $request->user(),
            $data['name'],
            $data['description'] ?? null,
        );

        return response()->json(['data' => $this->transform($project)], 201);
    }

    public function show(Request $request, int $project): JsonResponse
    {
        $record = $this->find($request, $project);
        $this->authorize('view', $record);

        return response()->json(['data' => $this->transform($record)]);
    }

    public function update(Request $request, int $project): JsonResponse
    {
        $record = $this->find($request, $project);
        $this->authorize('update', $record);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $record->fill($data)->save();

        return response()->json(['data' => $this->transform($record)]);
    }

    public function destroy(Request $request, int $project, ProjectService $projects): JsonResponse
    {
        $team = $this->team($request);
        $record = $this->find($request, $project);
        $this->authorize('delete', $record);
        $projects->delete($team, $request->user(), $record);

        return response()->json(status: 204);
    }

    private function team(Request $request): Team
    {
        /** @var Team $team */
        $team = $request->attributes->get('team');

        return $team;
    }

    private function find(Request $request, int $project): Project
    {
        return $this->team($request)->projects()->whereKey($project)->firstOrFail();
    }

    /**
     * @return array{id: int, name: string, description: string|null, created_at: string|null}
     */
    private function transform(Project $project): array
    {
        return [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'created_at' => $project->created_at?->toIso8601String(),
        ];
    }
}
