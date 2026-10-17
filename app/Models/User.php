<?php

namespace App\Models;

use App\Enums\TeamRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->using(TeamMembership::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function belongsToTeam(Team $team): bool
    {
        return $this->roleOn($team) !== null;
    }

    public function roleOn(Team $team): ?TeamRole
    {
        $membership = $this->relationLoaded('teams')
            ? $this->teams->firstWhere('id', $team->id)
            : $this->teams()->whereKey($team->id)->first();

        $role = $membership?->pivot?->role;

        if ($role instanceof TeamRole) {
            return $role;
        }

        return is_string($role) ? TeamRole::from($role) : null;
    }

    /**
     * @param  TeamRole|list<TeamRole>  $roles
     */
    public function hasTeamRole(Team $team, TeamRole|array $roles): bool
    {
        $role = $this->roleOn($team);

        if ($role === null) {
            return false;
        }

        $allowed = is_array($roles) ? $roles : [$roles];

        return in_array($role, $allowed, true);
    }
}
