<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

use function to_route;

final class SubscriptionController extends Controller
{
    public function index(): Response
    {
        $subscriptions = Subscription::orderBy('title')->get();

        $grouped = $subscriptions->groupBy('currency');

        $totals = $grouped->map(function ($subs, $currency) {
            $monthly = $subs->sum(fn (Subscription $sub) => match ($sub->frequency) {
                'yearly' => $sub->amount / 12,
                'quarterly' => $sub->amount / 3,
                default => $sub->amount,
            });

            return [
                'currency' => $currency,
                'monthly' => round($monthly, 2),
                'yearly' => round($monthly * 12, 2),
            ];
        })->values();

        return inertia('Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'totals' => $totals,
        ]);
    }

    public function create(): Response
    {
        return inertia('Subscriptions/Create');
    }

    public function store(SubscriptionRequest $request): RedirectResponse
    {
        Subscription::create($request->validated());

        return to_route('subscriptions.index')->with('success', 'Subscription added successfully.');
    }

    public function edit(Subscription $subscription): Response
    {
        return inertia('Subscriptions/Edit', [
            'subscription' => $subscription,
        ]);
    }

    public function update(SubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $subscription->update($request->validated());

        return to_route('subscriptions.index')->with('success', 'Subscription updated successfully.');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        $subscription->delete();

        return to_route('subscriptions.index')->with('success', 'Subscription deleted successfully.');
    }
}
