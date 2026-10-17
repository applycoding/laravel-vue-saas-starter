<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(
        ?Team $team,
        ?User $user,
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
    ): ActivityLog {
        return ActivityLog::query()->create([
            'team_id' => $team?->id,
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties === [] ? null : $properties,
        ]);
    }
}
