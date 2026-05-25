<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Date;

it('shows categories with totals for the current financial year', function () {
    $user = User::factory()->create();
    $grocery = Category::factory()->create(['name' => 'Grocery']);
    $transport = Category::factory()->create(['name' => 'Transport']);

    Expense::factory()->create(['category_id' => $grocery->id, 'amount' => 5000, 'date' => Date::today()]);
    Expense::factory()->create(['category_id' => $grocery->id, 'amount' => 3000, 'date' => Date::today()]);
    Expense::factory()->create(['category_id' => $transport->id, 'amount' => 2000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/explore')
        ->assertSee('Grocery')
        ->assertSee('Transport')
        ->assertSee('₹10,000.00');
});

it('shows empty state when no expenses exist', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/explore')
        ->assertSee('No expenses recorded for');
});

it('filters by financial year via url parameter', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Grocery']);

    Expense::factory()->create(['category_id' => $category->id, 'amount' => 7500, 'date' => '2024-06-15']);

    loginAs($user->email)
        ->navigate('/explore?fy=2024')
        ->assertSee('FY 2024-25')
        ->assertSee('Grocery');
});

it('updates total when toggling a category off', function () {
    $user = User::factory()->create();
    $grocery = Category::factory()->create(['name' => 'Grocery']);
    $transport = Category::factory()->create(['name' => 'Transport']);

    Expense::factory()->create(['category_id' => $grocery->id, 'amount' => 5000, 'date' => Date::today()]);
    Expense::factory()->create(['category_id' => $transport->id, 'amount' => 3000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/explore')
        ->assertSee('₹8,000.00')
        ->click('@category-'.$grocery->id)
        ->assertSee('₹3,000.00')
        ->assertSee('1 of 2 categories');
});

it('switches between include and exclude mode', function () {
    $user = User::factory()->create();
    $grocery = Category::factory()->create(['name' => 'Grocery']);
    $transport = Category::factory()->create(['name' => 'Transport']);

    Expense::factory()->create(['category_id' => $grocery->id, 'amount' => 5000, 'date' => Date::today()]);
    Expense::factory()->create(['category_id' => $transport->id, 'amount' => 3000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/explore')
        ->assertSee('₹8,000.00')
        ->click('@category-'.$grocery->id)
        ->assertSee('₹3,000.00')
        ->assertSee('1 of 2 categories')
        ->click('Exclude')
        ->assertSee('₹3,000.00')
        ->assertSee('1 of 2 categories');
});

it('selects all and none categories', function () {
    $user = User::factory()->create();
    $grocery = Category::factory()->create(['name' => 'Grocery']);
    $transport = Category::factory()->create(['name' => 'Transport']);

    Expense::factory()->create(['category_id' => $grocery->id, 'amount' => 5000, 'date' => Date::today()]);
    Expense::factory()->create(['category_id' => $transport->id, 'amount' => 3000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/explore')
        ->assertSee('₹8,000.00')
        ->click('None')
        ->assertSee('₹0.00')
        ->assertSee('No categories')
        ->click('All')
        ->assertSee('₹8,000.00')
        ->assertSee('All categories');
});
