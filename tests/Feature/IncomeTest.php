<?php

use App\Models\Income;
use App\Models\Source;
use App\Models\User;
use Illuminate\Support\Facades\Date;

it('shows validation errors for store income', function (array $data, array $invalid, array $valid) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/income', $data)
        ->assertInvalid($invalid)
        ->assertValid($valid);
})->with(([
    'empty fields' => [[], ['date', 'amount', 'source_id'], []],
    'wrong input' => [['abc', 'abc', 'abc'], ['date', 'amount', 'source_id'], []],
]));

it('shows validation errors for update income', function (array $data, array $invalid, array $valid) {
    $user = User::factory()->create();
    $income = Income::factory()->create();

    $this->actingAs($user)
        ->put('/income/'.$income->id, $data)
        ->assertInvalid($invalid)
        ->assertValid($valid);
})->with(([
    'empty fields' => [[], ['date', 'amount', 'source_id'], []],
    'wrong input' => [['abc', 'abc', 'abc'], ['date', 'amount', 'source_id'], []],
]));

it('can create a new income', function ($date, $amount) {
    $user = User::factory()->create();
    $source = Source::factory()->create();

    $this->actingAs($user)
        ->post('/income', [
            'date' => $date,
            'source_id' => $source->id,
            'amount' => $amount,
        ]);

    $this->assertDatabaseCount('incomes', 1);
    $this->assertDatabaseHas('incomes', [
        'date' => $date,
        'amount' => $amount,
        'source_id' => $source->id,
    ]);
})->with([
    [Date::now()->format('Y-m-d'), 5000],
    [Date::now()->format('Y-m-d'), 5000.50],
    [Date::now()->format('Y-m-d'), 75000],
    [Date::now()->format('Y-m-d'), 75000.89],
]);

it('can update an income', function ($date, $amount) {
    $user = User::factory()->create();
    $source = Source::factory()->create();
    $income = Income::factory()->create();

    $this->actingAs($user)
        ->put('/income/'.$income->id, [
            'date' => $date,
            'source_id' => $source->id,
            'amount' => $amount,
        ]);

    $this->assertDatabaseCount('incomes', 1);
    $this->assertDatabaseHas('incomes', [
        'date' => $date,
        'amount' => $amount,
        'source_id' => $source->id,
    ]);
})->with([
    [Date::now()->format('Y-m-d'), 5000],
    [Date::now()->format('Y-m-d'), 5000.50],
    [Date::now()->format('Y-m-d'), 75000],
    [Date::now()->format('Y-m-d'), 75000.89],
]);

it('can delete an income', function () {
    $user = User::factory()->create();
    $income = Income::factory()->create();

    $this->actingAs($user)
        ->delete('/income/'.$income->id);

    $this->assertCount(0, Income::all());
});

it('lists income for the current financial year by default', function () {
    $user = User::factory()->create();
    $source = Source::factory()->create();

    // Current FY income (April-ish of current FY)
    Income::factory()->create([
        'source_id' => $source->id,
        'amount' => 10000,
        'date' => Date::today()->startOfMonth(),
    ]);

    // Income from two FYs ago — should not be included
    Income::factory()->create([
        'source_id' => $source->id,
        'amount' => 99999,
        'date' => Date::today()->subYears(2),
    ]);

    $this->actingAs($user)
        ->get('/income')
        ->assertInertia(fn ($page) => $page
            ->component('Income/Index')
            ->where('total', 10000)
        );
});

it('filters income by the provided financial year', function () {
    $user = User::factory()->create();
    $source = Source::factory()->create();

    // Today is 2026-04-21 (FY 2026-27). Create an income in FY 2024-25.
    Income::factory()->create([
        'source_id' => $source->id,
        'amount' => 42000,
        'date' => '2024-06-15',
    ]);

    $this->actingAs($user)
        ->get('/income?fy=2024')
        ->assertInertia(fn ($page) => $page
            ->component('Income/Index')
            ->where('fy', 2024)
            ->where('fyLabel', 'FY 2024-25')
            ->where('total', 42000)
        );
});
