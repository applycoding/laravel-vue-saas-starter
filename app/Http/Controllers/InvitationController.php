<?php

namespace App\Http\Controllers;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    public function store(Request $request, Team $team, InvitationService $invitations): RedirectResponse
    {
        $this->authorize('invite', $team);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in([TeamRole::Admin->value, TeamRole::Member->value])],
        ]);

        $invitation = $invitations->invite(
            $team,
            $request->user(),
            $data['email'],
            TeamRole::from($data['role']),
        );

        return back()->with('success', "Invitation ready for {$invitation->email}. Share the accept link from the pending list. This demo does not send email.");
    }

    public function destroy(Request $request, TeamInvitation $invitation, InvitationService $invitations): RedirectResponse
    {
        $team = $invitation->team;
        $this->authorize('invite', $team);
        $invitations->revoke($team, $request->user(), $invitation);

        return back()->with('success', 'Invitation revoked.');
    }

    public function show(Request $request, string $token): Response
    {
        $invitation = TeamInvitation::query()->where('token', $token)->firstOrFail();

        return Inertia::render('Invitations/Accept', [
            'invitation' => [
                'email' => $invitation->email,
                'role' => $invitation->role instanceof TeamRole ? $invitation->role->value : $invitation->role,
                'team' => $invitation->team->name,
                'expired' => ! $invitation->isPending(),
                'matches_user' => strcasecmp($request->user()->email, $invitation->email) === 0,
            ],
            'token' => $token,
        ]);
    }

    public function accept(Request $request, string $token, InvitationService $invitations): RedirectResponse
    {
        $invitation = TeamInvitation::query()->where('token', $token)->firstOrFail();
        $invitations->accept($request->user(), $invitation);

        return redirect()->route('teams.index')->with('success', "You joined {$invitation->team->name}.");
    }
}
