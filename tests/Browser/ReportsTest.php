<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Support\Facades\Date;

it('shows the cashflow summary for the financial year', function () {
    $user = User::factory()->create();

    Expense::factory()->create(['amount' => 40000, 'date' => Date::today()]);
    Income::factory()->create(['amount' => 100000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertNoJavaScriptErrors()
        ->assertSee('Income')
        ->assertSee('₹1L')
        ->assertSee('Spent')
        ->assertSee('₹40K')
        ->assertSee('Saved')
        ->assertSee('₹60K')
        ->assertSee('60% of income');
});

it('shows overspending when expenses exceed income', function () {
    $user = User::factory()->create();

    Expense::factory()->create(['amount' => 50000, 'date' => Date::today()]);
    Income::factory()->create(['amount' => 30000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSee('Overspent')
        ->assertSee('₹20K');
});

it('shows monthly cashflow rows', function () {
    $user = User::factory()->create();

    $fyStartYear = Date::today()->month >= 4 ? Date::today()->year : Date::today()->year - 1;

    Expense::factory()->create(['amount' => 5000, 'date' => "{$fyStartYear}-04-10"]);
    Income::factory()->create(['amount' => 8000, 'date' => "{$fyStartYear}-04-05"]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSee('Monthly cashflow')
        ->assertSeeIn('[data-testid="cashflow-'.$fyStartYear.'-04"]', '₹5K')
        ->assertSeeIn('[data-testid="cashflow-'.$fyStartYear.'-04"]', '+₹3K');
});

it('shows category trends with totals', function () {
    $user = User::factory()->create();
    $grocery = Category::factory()->create(['name' => 'Grocery']);

    Expense::factory()->count(3)->create([
        'category_id' => $grocery->id,
        'amount' => 1000,
        'date' => Date::today(),
    ]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSee('Category trends')
        ->assertSee('Grocery')
        ->assertSee('₹3K');
});

it('lists the biggest expenses with notes', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Shopping']);

    Expense::factory()->create([
        'category_id' => $category->id,
        'amount' => 84999,
        'note' => 'New phone',
        'date' => Date::today(),
    ]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSee('Biggest expenses')
        ->assertSee('New phone')
        ->assertSee('₹84,999');
});

it('filters by financial year via the url', function () {
    $user = User::factory()->create();

    Expense::factory()->create(['amount' => 7500, 'date' => '2024-06-15']);

    loginAs($user->email)
        ->navigate('/reports?fy=2024')
        ->assertSee('FY 2024-25')
        ->assertSee('₹7.5K');
});

it('shows an empty state when nothing is recorded', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSee('Nothing recorded in');
});
