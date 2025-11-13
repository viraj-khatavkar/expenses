<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Date;

it('can see expenses of only current and last month on index page', function () {
    Expense::factory()->create(['amount' => 100]);
    Expense::factory()->create(['amount' => 50]);
    Expense::factory()->create(['date' => Date::now()->subMonths(3), 'amount' => 25]);

    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/expenses')
        ->assertSee('100')
        ->assertSee('50')
        ->assertDontSee('25');
});

it('can see correct total on index page', function ($amountOne, $amountTwo, $total) {
    Expense::factory()->create(['amount' => $amountOne]);
    Expense::factory()->create(['amount' => $amountTwo]);
    Expense::factory()->create(['date' => Date::now()->subMonths(3), 'amount' => random_int(1000, 10000)]);

    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/expenses')
        ->assertSee($total);
})->with([
    [100, 50, '150'],
    [1000, 550, '1,550'],
    [1000.27, 500.18, '1,500.45'],
    [345000, 150, '3,45,150'],
]);

it('can add a new expense', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    loginAs($user->email)
        ->navigate('/expenses')
        ->click('@add-expense-button')
        ->assertPathIs('/expenses/create')
        ->type('amount', '1000')
        ->select('category_id', $category->id)
        ->press('Add Expense')
        ->assertPathIs('/expenses')
        ->assertSee('1,000')
        ->assertSee($category->name);
});

it('can edit an expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create(['amount' => 100]);

    loginAs($user->email)
        ->navigate('/expenses')
        ->assertSee('100')
        ->assertSee($expense->category->name)
        ->click($expense->category->name)
        ->assertPathIs('/expenses/'.$expense->id.'/edit')
        ->type('amount', '1000')
        ->press('Update')
        ->assertPathIs('/expenses')
        ->assertSee('Expense updated successfully.')
        ->assertSee('1,000')
        ->assertDontSee('100');
});

it('can delete an expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create(['amount' => 100]);

    loginAs($user->email)
        ->navigate('/expenses')
        ->assertSee('100')
        ->assertSee($expense->category->name)
        ->click($expense->category->name)
        ->assertPathIs('/expenses/'.$expense->id.'/edit')
        ->press('Delete')
        ->assertSee('Expense deleted successfully.')
        ->assertPathIs('/expenses')
        ->assertDontSee('100');
});

it('can see validation errors for creating a new expense', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/expenses')
        ->click('@add-expense-button')
        ->assertPathIs('/expenses/create')
        ->type('date', '')
        ->press('Add Expense')
        ->assertPathIs('/expenses/create')
        ->assertSee('The date field is required')
        ->assertSee('The category field is required')
        ->assertSee('The amount field is required');
});
