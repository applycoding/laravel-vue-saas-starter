<?php

namespace App\Models;

use App\Enums\TeamRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInvitation extends Model
{
    protected $fillable = [
        'team_id',
        'email',
        'role',
        'token',
        'invited_by',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => TeamRole::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * @param  Builder<TeamInvitation>  $query
     * @return Builder<TeamInvitation>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('accepted_at')->where('expires_at', '>', now());
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && $this->expires_at !== null && $this->expires_at->isFuture();
    }
}
