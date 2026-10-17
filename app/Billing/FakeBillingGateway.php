<?php

namespace App\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JsonException;

class FakeBillingGateway implements BillingGateway
{
    public function __construct(private readonly string $webhookSecret) {}

    public function createCheckout(Team $team, Plan $plan): CheckoutSession
    {
        $id = 'cs_'.Str::lower(Str::random(20));

        Cache::put($this->cacheKey($id), [
            'team_id' => $team->id,
            'plan' => $plan->slug,
        ], now()->addHour());

        return new CheckoutSession(
            id: $id,
            url: route('billing.fake-checkout', ['session' => $id]),
            teamId: $team->id,
            planSlug: $plan->slug,
        );
    }

    public function cancel(Subscription $subscription): BillingEvent
    {
        return new BillingEvent(
            id: 'evt_local_cancel_'.$subscription->id.'_'.Str::lower(Str::random(8)),
            type: 'customer.subscription.deleted',
            teamId: $subscription->team_id,
            planSlug: $subscription->plan?->slug,
            gatewaySubscriptionId: $subscription->gateway_subscription_id,
        );
    }

    public function parseWebhook(string $payload, ?string $signature): BillingEvent
    {
        $this->assertSignature($payload, $signature);

        try {
            $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new \InvalidArgumentException('Billing payload is not valid JSON.', previous: $exception);
        }

        if (! is_array($data)) {
            throw new \InvalidArgumentException('Billing payload must be a JSON object.');
        }

        return $this->eventFromArray($data);
    }

    /**
     * @param  array<string, mixed>  $session
     */
    public function pullCheckout(string $sessionId): ?array
    {
        $session = Cache::get($this->cacheKey($sessionId));

        return is_array($session) ? $session : null;
    }

    public function forgetCheckout(string $sessionId): void
    {
        Cache::forget($this->cacheKey($sessionId));
    }

    /**
     * @param  array<string, mixed>  $session
     */
    public function eventFromCheckout(string $sessionId, array $session): BillingEvent
    {
        $teamId = $session['team_id'] ?? null;
        $plan = $session['plan'] ?? null;

        if (! is_int($teamId) && ! (is_string($teamId) && ctype_digit($teamId))) {
            throw new \InvalidArgumentException('Checkout session is missing a team.');
        }

        if (! is_string($plan) || $plan === '') {
            throw new \InvalidArgumentException('Checkout session is missing a plan.');
        }

        return new BillingEvent(
            id: 'evt_local_'.$sessionId,
            type: 'checkout.session.completed',
            teamId: (int) $teamId,
            planSlug: $plan,
            gatewaySubscriptionId: 'sub_'.$sessionId,
        );
    }

    public function sign(string $payload, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $digest = hash_hmac('sha256', $timestamp.'.'.$payload, $this->webhookSecret);

        return 't='.$timestamp.',v1='.$digest;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function eventFromArray(array $data): BillingEvent
    {
        $type = $data['type'] ?? null;
        $object = $data['data']['object'] ?? null;

        if (! is_string($type) || $type === '' || ! is_array($object)) {
            throw new \InvalidArgumentException('Billing payload is missing type or data.object.');
        }

        $metadata = is_array($object['metadata'] ?? null) ? $object['metadata'] : [];
        $teamId = $metadata['team_id'] ?? $object['client_reference_id'] ?? null;

        if (! is_int($teamId) && ! (is_string($teamId) && ctype_digit((string) $teamId))) {
            throw new \InvalidArgumentException('Billing payload is missing metadata.team_id.');
        }

        $plan = $metadata['plan'] ?? null;
        $subscriptionId = $object['subscription'] ?? null;

        if ($type === 'customer.subscription.deleted' || $type === 'customer.subscription.updated') {
            $subscriptionId = is_string($object['id'] ?? null) ? $object['id'] : $subscriptionId;
        }

        return new BillingEvent(
            id: is_string($data['id'] ?? null) ? $data['id'] : '',
            type: $type,
            teamId: (int) $teamId,
            planSlug: is_string($plan) ? $plan : null,
            gatewaySubscriptionId: is_string($subscriptionId) ? $subscriptionId : null,
        );
    }

    private function assertSignature(string $payload, ?string $signature): void
    {
        if ($signature === null || $signature === '') {
            throw new BillingSignatureException('Missing signature.');
        }

        $parts = [];

        foreach (explode(',', $signature) as $piece) {
            [$key, $value] = array_pad(explode('=', $piece, 2), 2, null);
            if (is_string($key) && $key !== '') {
                $parts[$key] = $value;
            }
        }

        $timestamp = $parts['t'] ?? null;
        $given = $parts['v1'] ?? null;

        if (! is_string($timestamp) || ! ctype_digit($timestamp) || ! is_string($given) || $given === '') {
            throw new BillingSignatureException('Malformed signature.');
        }

        $tolerance = (int) config('billing.signature_tolerance', 300);

        if (abs(time() - (int) $timestamp) > $tolerance) {
            throw new BillingSignatureException('Signature timestamp is outside the tolerance window.');
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $this->webhookSecret);

        if (! hash_equals($expected, $given)) {
            throw new BillingSignatureException('Signature mismatch.');
        }
    }

    private function cacheKey(string $sessionId): string
    {
        if (! preg_match('/^cs_[A-Za-z0-9]+$/', $sessionId)) {
            throw new \InvalidArgumentException('Invalid checkout session id.');
        }

        return 'billing.checkout.'.$sessionId;
    }
}
