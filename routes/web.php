<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BillingWebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::post('/webhooks/billing', BillingWebhookController::class)->name('webhooks.billing');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::patch('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
    Route::post('/teams/{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');

    Route::post('/teams/{team}/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');
    Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

    Route::patch('/teams/{team}/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/teams/{team}/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/fake-checkout/{session}', [BillingController::class, 'showFakeCheckout'])->name('billing.fake-checkout');
    Route::post('/billing/fake-checkout/{session}', [BillingController::class, 'confirmFakeCheckout'])->name('billing.fake-checkout.confirm');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');

    Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');

    Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
