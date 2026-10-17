<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(TeamMembership::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function activity(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function currentPlan(): Plan
    {
        $this->loadMissing('subscription.plan');

        return $this->subscription?->plan ?? Plan::starter();
    }
}
