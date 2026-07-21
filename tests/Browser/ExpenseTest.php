<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Date;

it('shows only the selected month of expenses with navigation', function () {
    Expense::factory()->create(['amount' => 100]);
    Expense::factory()->create(['date' => Date::now()->subMonthNoOverflow(), 'amount' => 50]);
    Expense::factory()->create(['date' => Date::now()->subMonthsNoOverflow(3), 'amount' => 25]);

    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/expenses')
        ->assertSee(Date::now()->format('F Y'))
        ->assertSee('₹100')
        ->assertDontSee('₹50')
        ->click('@prev-month')
        ->assertSee('₹50')
        ->assertDontSee('₹100');
});

it('can open an older month directly via the url', function () {
    Expense::factory()->create(['date' => Date::now()->subMonthsNoOverflow(3), 'amount' => 25]);

    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/expenses?month='.Date::now()->subMonthsNoOverflow(3)->format('Y-m'))
        ->assertSee(Date::now()->subMonthsNoOverflow(3)->format('F Y'))
        ->assertSee('₹25');
});

it('shows an empty state for a month with no expenses', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/expenses')
        ->assertSee('No expenses in '.Date::now()->format('F Y'));
});

it('can see correct total on index page', function ($amountOne, $amountTwo, $total) {
    Expense::factory()->create(['amount' => $amountOne]);
    Expense::factory()->create(['amount' => $amountTwo]);
    Expense::factory()->create(['date' => Date::now()->subMonthsNoOverflow(3), 'amount' => random_int(1000, 10000)]);

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

it('can add a new expense', function ($amount, $displayed) {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    loginAs($user->email)
        ->navigate('/expenses')
        ->click('@add-expense-button')
        ->assertPathIs('/expenses/create')
        ->type('amount', (string) $amount)
        ->select('category_id', $category->id)
        ->press('Add Expense')
        ->assertPathIs('/expenses')
        ->assertSee($displayed)
        ->assertSee($category->name);
})->with([
    [200, '₹200'],
    [200.50, '₹200.50'],
    [11389, '₹11,389'],
    [11389.89, '₹11,389.89'],
]);

it('uses a numeric amount field that accepts decimals', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/expenses/create')
        ->assertAttribute('[name=amount]', 'type', 'number')
        ->assertAttribute('[name=amount]', 'step', '0.01')
        ->assertAttribute('[name=amount]', 'inputmode', 'decimal');
});

it('can add an expense with a note', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Shopping']);

    loginAs($user->email)
        ->navigate('/expenses/create')
        ->type('amount', '2499')
        ->select('category_id', $category->id)
        ->type('note', 'New headphones')
        ->press('Add Expense')
        ->assertPathIs('/expenses')
        ->assertSee('Shopping')
        ->assertSee('New headphones')
        ->assertSee('₹2,499');
});

it('returns to the month of a backdated expense after creating it', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $lastMonth = Date::now()->subMonthNoOverflow();

    loginAs($user->email)
        ->navigate('/expenses/create')
        ->fill('date', $lastMonth->format('Y-m-d'))
        ->type('amount', '777')
        ->select('category_id', $category->id)
        ->press('Add Expense')
        ->assertQueryStringHas('month', $lastMonth->format('Y-m'))
        ->assertSee('₹777');
});

it('can edit an expense', function ($amount, $displayed) {
    $user = User::factory()->create();
    $expense = Expense::factory()->create(['amount' => 99]);

    loginAs($user->email)
        ->navigate('/expenses')
        ->assertSee('₹99')
        ->assertSee($expense->category->name)
        ->click($expense->category->name)
        ->assertPathIs('/expenses/'.$expense->id.'/edit')
        ->type('amount', (string) $amount)
        ->press('Update')
        ->assertPathIs('/expenses')
        ->assertSee('Expense updated successfully.')
        ->assertSee($displayed)
        ->assertDontSee('₹99');
})->with([
    [200, '₹200'],
    [200.50, '₹200.50'],
]);

it('can delete an expense after confirming', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create(['amount' => 100]);

    loginAs($user->email)
        ->navigate('/expenses')
        ->assertSee('100')
        ->assertSee($expense->category->name)
        ->click($expense->category->name)
        ->assertPathIs('/expenses/'.$expense->id.'/edit')
        ->press('Delete')
        ->click('@confirm-delete')
        ->assertSee('Expense deleted successfully.')
        ->assertPathIs('/expenses')
        ->assertDontSee('100');
});

it('keeps the expense when the delete confirmation is cancelled', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create(['amount' => 100]);

    loginAs($user->email)
        ->navigate('/expenses/'.$expense->id.'/edit')
        ->press('Delete')
        ->press('Cancel')
        ->assertPathIs('/expenses/'.$expense->id.'/edit');

    expect(Expense::query()->count())->toBe(1);
});

it('can add a one-time expense', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Hospital']);

    loginAs($user->email)
        ->navigate('/expenses/create')
        ->type('amount', '3200')
        ->select('category_id', $category->id)
        ->check('is_one_time')
        ->press('Add Expense')
        ->assertPathIs('/expenses')
        ->assertSee('Hospital')
        ->assertSee('one-time')
        ->assertSee('₹3,200');

    expect(Expense::query()->first()->is_one_time)->toBeTrue();
});

it('persists unchecking one-time when editing an expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->oneTime()->create(['amount' => 150]);

    loginAs($user->email)
        ->navigate('/expenses/'.$expense->id.'/edit')
        ->uncheck('is_one_time')
        ->press('Update')
        ->assertPathIs('/expenses')
        ->assertSee('Expense updated successfully.')
        ->assertDontSee('one-time');

    expect($expense->fresh()->is_one_time)->toBeFalse();
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
