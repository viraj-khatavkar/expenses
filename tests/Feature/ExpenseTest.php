<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Date;

it('shows validation errors for store expense', function (array $data, array $invalid, array $valid) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/expenses', $data)
        ->assertInvalid($invalid)
        ->assertValid($valid);
})->with(([
    'empty fields' => [[], ['date', 'amount', 'category_id'], []],
    'wrong input' => [['abc', 'abc', 'abc'], ['date', 'amount', 'category_id'], []],
]));

it('shows validation errors for update expense', function (array $data, array $invalid, array $valid) {
    $user = User::factory()->create();
    $expense = Expense::factory()->create();

    $this->actingAs($user)
        ->put('/expenses/'.$expense->id, $data)
        ->assertInvalid($invalid)
        ->assertValid($valid);
})->with(([
    'empty fields' => [[], ['date', 'amount', 'category_id'], []],
    'wrong input' => [['abc', 'abc', 'abc'], ['date', 'amount', 'category_id'], []],
]));

it('can create a new expense', function ($date, $amount) {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user)
        ->post('/expenses', [
            'date' => $date,
            'category_id' => $category->id,
            'amount' => $amount,
        ]);

    $this->assertDatabaseCount('expenses', 1);
    $this->assertDatabaseHas('expenses', [
        'date' => $date,
        'amount' => $amount,
        'category_id' => $category->id,
    ]);
})->with([
    [Date::now()->format('Y-m-d'), 200],
    [Date::now()->format('Y-m-d'), 200.50],
    [Date::now()->format('Y-m-d'), 11389],
    [Date::now()->format('Y-m-d'), 11389.89],
]);

it('can update an expense', function ($date, $amount) {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $expense = Expense::factory()->create();

    $this->actingAs($user)
        ->put('/expenses/'.$expense->id, [
            'date' => $date,
            'category_id' => $category->id,
            'amount' => $amount,
        ]);

    $this->assertDatabaseCount('expenses', 1);
    $this->assertDatabaseHas('expenses', [
        'date' => $date,
        'amount' => $amount,
        'category_id' => $category->id,
    ]);
})->with([
    [Date::now()->format('Y-m-d'), 200],
    [Date::now()->format('Y-m-d'), 200.50],
    [Date::now()->format('Y-m-d'), 11389],
    [Date::now()->format('Y-m-d'), 11389.89],
]);

it('can delete an expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create();

    $this->actingAs($user)
        ->delete('/expenses/'.$expense->id);

    $this->assertCount(0, Expense::all());
});
