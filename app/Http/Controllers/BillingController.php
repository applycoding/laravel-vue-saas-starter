<?php

namespace App\Http\Controllers;

use App\Billing\BillingGateway;
use App\Billing\FakeBillingGateway;
use App\Billing\WebhookProcessor;
use App\Http\Controllers\Concerns\ResolvesCurrentTeam;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    use ResolvesCurrentTeam;

    public function index(Request $request): Response
    {
        $team = $this->currentTeam($request);
        $subscription = $team->subscription()->with('plan')->first();

        return Inertia::render('Billing/Index', [
            'plans' => Plan::query()->orderBy('price_cents')->get(['id', 'name', 'slug', 'price_cents', 'max_members', 'max_projects', 'description']),
            'subscription' => $subscription === null ? null : [
                'status' => $subscription->status->value,
                'plan_slug' => $subscription->plan->slug,
                'gateway' => $subscription->gateway,
                'gateway_subscription_id' => $subscription->gateway_subscription_id,
            ],
            'canManage' => $request->user()->can('manageBilling', $team),
        ]);
    }

    public function checkout(Request $request, BillingGateway $gateway): RedirectResponse
    {
        $team = $this->currentTeam($request);
        $this->authorize('manageBilling', $team);

        $data = $request->validate([
            'plan' => ['required', 'exists:plans,slug'],
        ]);

        $plan = Plan::query()->where('slug', $data['plan'])->firstOrFail();

        if ($plan->price_cents === 0) {
            return back()->with('error', 'Starter is already the free plan. Choose a paid plan to open a fake checkout.');
        }

        $session = $gateway->createCheckout($team, $plan);

        return redirect()->to($session->url);
    }

    public function showFakeCheckout(Request $request, string $session, BillingGateway $gateway): Response
    {
        $stored = $this->storedSession($gateway, $session);
        $team = $this->currentTeam($request);
        $this->authorize('manageBilling', $team);
        abort_unless((int) $stored['team_id'] === $team->id, 403);

        $plan = Plan::query()->where('slug', $stored['plan'])->firstOrFail();

        return Inertia::render('Billing/FakeCheckout', [
            'sessionId' => $session,
            'plan' => [
                'name' => $plan->name,
                'slug' => $plan->slug,
                'price_cents' => $plan->price_cents,
                'description' => $plan->description,
            ],
        ]);
    }

    public function confirmFakeCheckout(
        Request $request,
        string $session,
        BillingGateway $gateway,
        WebhookProcessor $processor,
    ): RedirectResponse {
        $stored = $this->storedSession($gateway, $session);
        $team = $this->currentTeam($request);
        $this->authorize('manageBilling', $team);
        abort_unless((int) $stored['team_id'] === $team->id, 403);
        abort_unless($gateway instanceof FakeBillingGateway, 404);

        $processor->handle($gateway->eventFromCheckout($session, $stored));
        $gateway->forgetCheckout($session);

        return redirect()->route('billing.index')->with('success', 'Fake payment recorded. The team plan is updated.');
    }

    public function cancel(Request $request, BillingGateway $gateway, WebhookProcessor $processor): RedirectResponse
    {
        $team = $this->currentTeam($request);
        $this->authorize('manageBilling', $team);

        $subscription = $team->subscription;

        if ($subscription === null || $subscription->plan?->slug === 'starter') {
            return back()->with('error', 'This team is already on the Starter plan.');
        }

        $processor->handle($gateway->cancel($subscription));

        return back()->with('success', 'Subscription canceled. The team is back on Starter.');
    }

    /**
     * @return array<string, mixed>
     */
    private function storedSession(BillingGateway $gateway, string $session): array
    {
        abort_unless($gateway instanceof FakeBillingGateway, 404);

        $stored = $gateway->pullCheckout($session);

        abort_if($stored === null, 404);

        return $stored;
    }
}
