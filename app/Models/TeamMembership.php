<?php

namespace App\Models;

use App\Enums\TeamRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamMembership extends Pivot
{
    protected $table = 'team_user';

    protected function casts(): array
    {
        return [
            'role' => TeamRole::class,
        ];
    }
}
