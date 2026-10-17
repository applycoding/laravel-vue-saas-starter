<?php

namespace App\Services;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeamService
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly ActivityLogger $activity,
    ) {}

    public function create(User $owner, string $name): Team
    {
        return DB::transaction(function () use ($owner, $name): Team {
            $team = Team::query()->create([
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
            ]);

            $team->members()->attach($owner->id, [
                'role' => TeamRole::Owner->value,
            ]);

            $this->subscriptions->startOnStarter($team);

            $owner->forceFill(['current_team_id' => $team->id])->save();

            $this->activity->log(
                $team,
                $owner,
                'team.created',
                "Created team {$team->name}.",
                $team,
            );

            return $team->fresh(['subscription.plan']);
        });
    }

    public function switch(User $user, Team $team): void
    {
        abort_unless($user->belongsToTeam($team), 403);

        $user->forceFill(['current_team_id' => $team->id])->save();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'team';
        $slug = $base;
        $suffix = 2;

        while (Team::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
