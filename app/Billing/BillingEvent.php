<?php

namespace App\Billing;

final readonly class BillingEvent
{
    public function __construct(
        public string $id,
        public string $type,
        public int $teamId,
        public ?string $planSlug,
        public ?string $gatewaySubscriptionId,
    ) {}
}
