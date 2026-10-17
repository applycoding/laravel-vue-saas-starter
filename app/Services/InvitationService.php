<?php

namespace App\Services;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    public function __construct(
        private readonly FeatureGate $features,
        private readonly ActivityLogger $activity,
    ) {}

    public function invite(Team $team, User $inviter, string $email, TeamRole $role): TeamInvitation
    {
        if ($role === TeamRole::Owner) {
            throw ValidationException::withMessages([
                'role' => 'Ownership cannot be granted through an invitation.',
            ]);
        }

        $email = Str::lower($email);

        if ($team->members()->whereRaw('lower(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'That person is already on the team.',
            ]);
        }

        if ($team->invitations()->pending()->whereRaw('lower(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An invitation is already waiting for that email.',
            ]);
        }

        $this->features->assertCanInvite($team);

        $invitation = $team->invitations()->create([
            'email' => $email,
            'role' => $role,
            'token' => Str::random(40),
            'invited_by' => $inviter->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->activity->log(
            $team,
            $inviter,
            'member.invited',
            "Invited {$email} as {$role->label()}.",
            $invitation,
        );

        return $invitation;
    }

    public function accept(User $user, TeamInvitation $invitation): void
    {
        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation has expired or was already used.',
            ]);
        }

        if (Str::lower($user->email) !== Str::lower($invitation->email)) {
            abort(403, 'This invitation was sent to a different email address.');
        }

        $team = $invitation->team;

        if ($user->belongsToTeam($team)) {
            $invitation->forceFill(['accepted_at' => now()])->save();

            return;
        }

        $this->features->assertCanAddMember($team);

        DB::transaction(function () use ($user, $invitation, $team): void {
            $team->members()->attach($user->id, [
                'role' => $invitation->role instanceof TeamRole ? $invitation->role->value : $invitation->role,
            ]);

            $invitation->forceFill(['accepted_at' => now()])->save();

            if ($user->current_team_id === null) {
                $user->forceFill(['current_team_id' => $team->id])->save();
            }
        });

        $this->activity->log(
            $team,
            $user,
            'member.joined',
            "{$user->name} joined the team.",
            $team,
        );
    }

    public function revoke(Team $team, User $actor, TeamInvitation $invitation): void
    {
        abort_unless($invitation->team_id === $team->id, 404);

        $email = $invitation->email;
        $invitation->delete();

        $this->activity->log(
            $team,
            $actor,
            'member.invite_revoked',
            "Revoked the invitation for {$email}.",
        );
    }
}
