<?php

namespace App\Billing;

final readonly class CheckoutSession
{
    public function __construct(
        public string $id,
        public string $url,
        public int $teamId,
        public string $planSlug,
    ) {}
}
