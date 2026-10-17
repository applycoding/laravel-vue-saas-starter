<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Services\FeatureGate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function show(Request $request, FeatureGate $features): JsonResponse
    {
        /** @var Team $team */
        $team = $request->attributes->get('team');
        $plan = $team->currentPlan();

        return response()->json([
            'data' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'role' => $request->user()->roleOn($team)?->value,
                'plan' => [
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'max_members' => $plan->max_members,
                    'max_projects' => $plan->max_projects,
                ],
                'usage' => $features->snapshot($team),
            ],
        ]);
    }
}
