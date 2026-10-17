<?php

namespace App\Billing;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Team;
use App\Services\ActivityLogger;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class WebhookProcessor
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly ActivityLogger $activity,
    ) {}

    public function handle(BillingEvent $event): void
    {
        $cacheKey = $event->id !== '' ? 'billing.event.'.$event->id : null;

        if ($cacheKey !== null && Cache::has($cacheKey)) {
            return;
        }

        match ($event->type) {
            'checkout.session.completed', 'customer.subscription.updated' => $this->activate($event),
            'customer.subscription.deleted' => $this->revertToStarter($event),
            default => null,
        };

        if ($cacheKey !== null) {
            Cache::put($cacheKey, true, now()->addDay());
        }
    }

    private function activate(BillingEvent $event): void
    {
        $team = $this->team($event);
        $plan = Plan::query()->where('slug', $event->planSlug)->first();

        if ($plan === null) {
            throw new InvalidArgumentException('Billing event names an unknown plan.');
        }

        $this->subscriptions->changePlan(
            $team,
            $plan,
            SubscriptionStatus::Active,
            $event->gatewaySubscriptionId,
        );

        $this->activity->log(
            $team,
            null,
            'subscription.updated',
            "Plan changed to {$plan->name}.",
            $team,
            ['plan' => $plan->slug, 'event' => $event->type],
        );
    }

    private function revertToStarter(BillingEvent $event): void
    {
        $team = $this->team($event);
        $starter = Plan::starter();

        $this->subscriptions->changePlan($team, $starter, SubscriptionStatus::Active, null);

        $this->activity->log(
            $team,
            null,
            'subscription.updated',
            'Subscription canceled. The team is back on the Starter plan.',
            $team,
            ['plan' => $starter->slug, 'event' => $event->type],
        );
    }

    private function team(BillingEvent $event): Team
    {
        $team = Team::query()->find($event->teamId);

        if ($team === null) {
            throw new InvalidArgumentException('Billing event names an unknown team.');
        }

        return $team;
    }
}
