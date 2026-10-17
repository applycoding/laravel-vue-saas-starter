<?php

use App\Billing\BillingGateway;
use App\Billing\FakeBillingGateway;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function provisionTeam(User $owner, string $name = 'Acme'): Team
{
    return app(TeamService::class)->create($owner, $name);
}

function addTeammate(Team $team, TeamRole $role): User
{
    $user = User::factory()->create();
    $team->members()->attach($user->id, ['role' => $role->value]);
    $user->forceFill(['current_team_id' => $team->id])->save();

    return $user->fresh();
}

function billingPayload(string $fixture, array $replace): string
{
    $payload = (string) file_get_contents(base_path('tests/Fixtures/billing/'.$fixture.'.json'));

    return trim(str_replace(array_keys($replace), array_map('strval', array_values($replace)), $payload));
}

function signBillingPayload(string $payload): string
{
    $gateway = app(BillingGateway::class);

    if (! $gateway instanceof FakeBillingGateway) {
        throw new RuntimeException('Tests expect the fake billing gateway.');
    }

    return $gateway->sign($payload);
}

function postBillingWebhook(string $payload, ?string $signature = null): TestResponse
{
    return test()->call('POST', '/webhooks/billing', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_BILLING_SIGNATURE' => $signature ?? signBillingPayload($payload),
    ], $payload);
}
