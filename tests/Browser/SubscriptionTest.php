<?php

use App\Models\Subscription;
use App\Models\User;

it('lists subscriptions on the index page', function () {
    $user = User::factory()->create();

    Subscription::factory()->create([
        'title' => 'Netflix',
        'amount' => 649,
        'currency' => 'INR',
        'frequency' => 'monthly',
    ]);

    loginAs($user->email)
        ->navigate('/subscriptions')
        ->assertSee('Netflix')
        ->assertSee('monthly');
});

it('shows correct totals per currency', function () {
    $user = User::factory()->create();

    Subscription::factory()->create(['amount' => 500, 'currency' => 'INR', 'frequency' => 'monthly']);
    Subscription::factory()->create(['amount' => 1200, 'currency' => 'INR', 'frequency' => 'quarterly']);
    Subscription::factory()->create(['amount' => 12, 'currency' => 'USD', 'frequency' => 'monthly']);
    Subscription::factory()->create(['amount' => 120, 'currency' => 'USD', 'frequency' => 'yearly']);

    loginAs($user->email)
        ->navigate('/subscriptions')
        ->assertSee('INR Subscriptions')
        ->assertSee('₹900')
        ->assertSee('₹10,800')
        ->assertSee('USD Subscriptions')
        ->assertSee('$22')
        ->assertSee('$264');
});

it('can add a new subscription', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/subscriptions')
        ->press('Add Subscription')
        ->assertPathIs('/subscriptions/create')
        ->type('title', 'Netflix')
        ->type('amount', '649')
        ->select('currency', 'INR')
        ->select('frequency', 'monthly')
        ->press('Add Subscription')
        ->assertPathIs('/subscriptions')
        ->assertSee('Subscription added successfully.')
        ->assertSee('Netflix')
        ->assertSee('₹649');
});

it('can edit a subscription', function () {
    $user = User::factory()->create();
    $subscription = Subscription::factory()->create([
        'title' => 'Old Title',
        'amount' => 100,
        'currency' => 'INR',
        'frequency' => 'monthly',
    ]);

    loginAs($user->email)
        ->navigate('/subscriptions')
        ->click('Old Title')
        ->assertPathIs('/subscriptions/'.$subscription->id.'/edit')
        ->type('title', 'New Title')
        ->type('amount', '999')
        ->select('currency', 'USD')
        ->select('frequency', 'yearly')
        ->press('Update')
        ->assertPathIs('/subscriptions')
        ->assertSee('Subscription updated successfully.')
        ->assertSee('New Title')
        ->assertSee('yearly')
        ->assertSee('$999')
        ->assertDontSee('Old Title');
});

it('can delete a subscription after confirming', function () {
    $user = User::factory()->create();
    $subscription = Subscription::factory()->create(['title' => 'Cancel Me']);

    loginAs($user->email)
        ->navigate('/subscriptions/'.$subscription->id.'/edit')
        ->press('Delete')
        ->click('@confirm-delete')
        ->assertPathIs('/subscriptions')
        ->assertSee('Subscription deleted successfully.')
        ->assertDontSee('Cancel Me');
});

it('shows validation errors for required fields on create', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/subscriptions/create')
        ->press('Add Subscription')
        ->assertPathIs('/subscriptions/create')
        ->assertSee('The title field is required')
        ->assertSee('The amount field is required');
});

it('blocks non-numeric amounts with a numeric input field', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/subscriptions/create')
        ->assertAttribute('[name=amount]', 'type', 'number')
        ->assertAttribute('[name=amount]', 'step', '0.01')
        ->assertAttribute('[name=amount]', 'inputmode', 'decimal');
});
