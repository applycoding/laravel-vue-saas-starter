<?php

use App\Enums\TeamRole;
use App\Models\User;

test('a signed checkout fixture moves the team onto the paid plan', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');

    $payload = billingPayload('checkout.session.completed', [
        '__TEAM_ID__' => $team->id,
        '__PLAN__' => 'pro',
    ]);

    postBillingWebhook($payload)
        ->assertOk()
        ->assertJson(['received' => true]);

    $subscription = $team->fresh()->subscription;

    expect($subscription->plan->slug)->toBe('pro')
        ->and($subscription->gateway_subscription_id)->toBe('sub_test_fixture');
});

test('a webhook with a bad signature is rejected', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');

    $payload = billingPayload('checkout.session.completed', [
        '__TEAM_ID__' => $team->id,
        '__PLAN__' => 'pro',
    ]);

    postBillingWebhook($payload, 't=1,v1=deadbeef')->assertStatus(400);

    expect($team->fresh()->currentPlan()->slug)->toBe('starter');
});

test('a subscription deleted fixture returns the team to starter', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');

    postBillingWebhook(billingPayload('checkout.session.completed', [
        '__TEAM_ID__' => $team->id,
        '__PLAN__' => 'business',
    ]))->assertOk();

    postBillingWebhook(billingPayload('customer.subscription.deleted', [
        '__TEAM_ID__' => $team->id,
    ]))->assertOk();

    expect($team->fresh()->currentPlan()->slug)->toBe('starter');
});

test('the owner can confirm a fake checkout', function () {
    $owner = User::factory()->create();
    provisionTeam($owner, 'Acme');

    $checkout = $this->actingAs($owner)->post(route('billing.checkout'), ['plan' => 'pro']);
    $checkout->assertRedirect();

    $this->actingAs($owner)->post($checkout->headers->get('Location'))
        ->assertRedirect(route('billing.index'));

    expect($owner->fresh()->currentTeam->currentPlan()->slug)->toBe('pro');
});

test('a member cannot open billing checkout', function () {
    $owner = User::factory()->create();
    $team = provisionTeam($owner, 'Acme');
    $member = addTeammate($team, TeamRole::Member);

    $this->actingAs($member)
        ->post(route('billing.checkout'), ['plan' => 'pro'])
        ->assertForbidden();
});
