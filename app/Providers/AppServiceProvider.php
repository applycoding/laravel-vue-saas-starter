<?php

namespace App\Providers;

use App\Billing\BillingGateway;
use App\Billing\FakeBillingGateway;
use App\Enums\BillingDriver;
use App\Models\User;
use App\Services\TeamService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BillingGateway::class, function (): BillingGateway {
            $driver = BillingDriver::from((string) config('billing.gateway'));

            return match ($driver) {
                BillingDriver::Fake => new FakeBillingGateway((string) config('billing.webhook_secret')),
                default => throw new \LogicException('Unhandled billing driver ['.$driver->value.'].'),
            };
        });
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Event::listen(Registered::class, function (Registered $event): void {
            $user = $event->user;

            if (! $user instanceof User) {
                return;
            }

            app(TeamService::class)->create($user, $user->name."'s team");
        });
    }
}
