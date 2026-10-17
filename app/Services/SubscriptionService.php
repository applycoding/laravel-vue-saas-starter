<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Team;

class SubscriptionService
{
    public function startOnStarter(Team $team): Subscription
    {
        return $this->changePlan($team, Plan::starter(), SubscriptionStatus::Active, null);
    }

    public function changePlan(
        Team $team,
        Plan $plan,
        SubscriptionStatus $status,
        ?string $gatewaySubscriptionId,
    ): Subscription {
        return Subscription::query()->updateOrCreate(
            ['team_id' => $team->id],
            [
                'plan_id' => $plan->id,
                'status' => $status,
                'gateway' => 'fake',
                'gateway_subscription_id' => $gatewaySubscriptionId,
                'ends_at' => null,
            ],
        );
    }
}
