<?php

namespace App\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;

interface BillingGateway
{
    public function createCheckout(Team $team, Plan $plan): CheckoutSession;

    public function cancel(Subscription $subscription): BillingEvent;

    /**
     * Verify a Stripe-style signature header and parse the JSON body.
     *
     * Header format: t={unix},v1={hmac_sha256}
     * Signed payload: "{timestamp}.{raw_body}"
     */
    public function parseWebhook(string $payload, ?string $signature): BillingEvent;
}
