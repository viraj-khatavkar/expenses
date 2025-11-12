<?php

use App\Models\Expense;
use App\Models\User;

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

it('shows validation errors for edit expense', function (array $data, array $invalid, array $valid) {
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
