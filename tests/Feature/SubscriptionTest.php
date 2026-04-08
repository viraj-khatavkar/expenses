<?php

use App\Models\Subscription;
use App\Models\User;

it('shows the subscriptions index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/subscriptions')
        ->assertOk();
});

it('shows validation errors for store subscription', function (array $data, array $invalid, array $valid) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/subscriptions', $data)
        ->assertInvalid($invalid)
        ->assertValid($valid);
})->with([
    'empty fields' => [[], ['title', 'amount', 'currency', 'frequency'], []],
    'invalid frequency' => [
        ['title' => 'Netflix', 'amount' => 649, 'currency' => 'INR', 'frequency' => 'weekly'],
        ['frequency'],
        ['title', 'amount', 'currency'],
    ],
    'invalid amount' => [
        ['title' => 'Netflix', 'amount' => 'abc', 'currency' => 'INR', 'frequency' => 'monthly'],
        ['amount'],
        ['title', 'currency', 'frequency'],
    ],
    'invalid currency' => [
        ['title' => 'Netflix', 'amount' => 649, 'currency' => 'EUR', 'frequency' => 'monthly'],
        ['currency'],
        ['title', 'amount', 'frequency'],
    ],
]);

it('can create a subscription', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/subscriptions', [
            'title' => 'Netflix',
            'amount' => 649,
            'currency' => 'INR',
            'frequency' => 'monthly',
        ]);

    $this->assertDatabaseHas('subscriptions', [
        'title' => 'Netflix',
        'amount' => 649,
        'currency' => 'INR',
        'frequency' => 'monthly',
    ]);
});

it('can update a subscription', function () {
    $user = User::factory()->create();
    $subscription = Subscription::factory()->create();

    $this->actingAs($user)
        ->put("/subscriptions/{$subscription->id}", [
            'title' => 'Updated',
            'amount' => 999,
            'currency' => 'USD',
            'frequency' => 'yearly',
        ]);

    $this->assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'title' => 'Updated',
        'amount' => 999,
        'currency' => 'USD',
        'frequency' => 'yearly',
    ]);
});

it('can delete a subscription', function () {
    $user = User::factory()->create();
    $subscription = Subscription::factory()->create();

    $this->actingAs($user)
        ->delete("/subscriptions/{$subscription->id}");

    $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
});

it('calculates correct totals per currency', function () {
    $user = User::factory()->create();

    Subscription::factory()->create(['amount' => 500, 'currency' => 'INR', 'frequency' => 'monthly']);
    Subscription::factory()->create(['amount' => 1200, 'currency' => 'INR', 'frequency' => 'quarterly']);
    Subscription::factory()->create(['amount' => 12, 'currency' => 'USD', 'frequency' => 'monthly']);
    Subscription::factory()->create(['amount' => 120, 'currency' => 'USD', 'frequency' => 'yearly']);

    $response = $this->actingAs($user)->get('/subscriptions');
    $response->assertOk();

    $totals = collect($response->original->getData()['page']['props']['totals']);

    // INR monthly: 500 + 1200/3 = 900
    $inr = $totals->firstWhere('currency', 'INR');
    $this->assertEquals(900, $inr['monthly']);
    $this->assertEquals(10800, $inr['yearly']);

    // USD monthly: 12 + 120/12 = 22
    $usd = $totals->firstWhere('currency', 'USD');
    $this->assertEquals(22, $usd['monthly']);
    $this->assertEquals(264, $usd['yearly']);
});
