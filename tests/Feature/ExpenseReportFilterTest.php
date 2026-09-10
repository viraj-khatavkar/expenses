<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    $this->withoutVite();
    $this->travelTo(Date::parse('2026-09-10'));
});

it('filters a date range and category with matching daily and overall totals', function () {
    $category = Category::factory()->create(['name' => 'Travel']);
    $first = Expense::factory()->for($category)->create(['date' => '2026-04-01', 'amount' => 100.25]);
    $last = Expense::factory()->for($category)->create(['date' => '2026-05-31', 'amount' => 200.50]);
    $sameDay = Expense::factory()->for($category)->create(['date' => '2026-05-31', 'amount' => 50.10]);
    Expense::factory()->for($category)->create(['date' => '2026-03-31', 'amount' => 900]);
    Expense::factory()->for($category)->create(['date' => '2026-06-01', 'amount' => 900]);
    Expense::factory()->create(['date' => '2026-04-01', 'amount' => 900]);
    Expense::factory()->for($category)->create(['date' => '2026-04-15', 'amount' => 900])->delete();

    $this->get(route('expenses.index', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-05-31',
        'category_id' => $category->id,
    ]))->assertSuccessful()->assertInertia(fn (Assert $page): Assert => $page
        ->component('Expenses/Index')
        ->where('isDateRange', true)
        ->where('isCurrentMonth', false)
        ->where('periodLabel', '01 Apr 2026 to 31 May 2026')
        ->where('categoryFilter.id', $category->id)
        ->where('categoryFilter.name', 'Travel')
        ->where('total', 350.85)
        ->has('expenseGroups', 2)
        ->where('expenseGroups.0.total', 250.6)
        ->has('expenseGroups.0.expenses', 2)
        ->where('expenseGroups.0.expenses.0.id', $sameDay->id)
        ->where('expenseGroups.0.expenses.1.id', $last->id)
        ->where('expenseGroups.1.total', 100.25)
        ->has('expenseGroups.1.expenses', 1)
        ->where('expenseGroups.1.expenses.0.id', $first->id)
    );
});

it('filters a selected month by category', function () {
    $category = Category::factory()->create();
    $expense = Expense::factory()->for($category)->create(['date' => '2026-08-31', 'amount' => 125.75]);
    Expense::factory()->for($category)->create(['date' => '2026-07-31', 'amount' => 900]);
    Expense::factory()->for($category)->create(['date' => '2026-09-01', 'amount' => 900]);
    Expense::factory()->create(['date' => '2026-08-31', 'amount' => 900]);

    $this->get(route('expenses.index', ['month' => '2026-08', 'category_id' => $category->id]))
        ->assertSuccessful()->assertInertia(fn (Assert $page): Assert => $page
        ->component('Expenses/Index')
        ->where('month', '2026-08')
        ->where('monthLabel', 'August 2026')
        ->where('periodLabel', 'August 2026')
        ->where('isCurrentMonth', false)
        ->where('isDateRange', false)
        ->where('categoryFilter.id', $category->id)
        ->where('total', 125.75)
        ->has('expenseGroups', 1)
        ->has('expenseGroups.0.expenses', 1)
        ->where('expenseGroups.0.expenses.0.id', $expense->id)
        );
});

it('shows all categories and inclusive dates for a date range without a category', function () {
    Expense::factory()->create(['date' => '2025-12-31', 'amount' => 100.25]);
    Expense::factory()->create(['date' => '2026-01-01', 'amount' => 200.50]);
    Expense::factory()->create(['date' => '2026-01-02', 'amount' => 900]);

    $this->get(route('expenses.index', ['start_date' => '2025-12-31', 'end_date' => '2026-01-01']))
        ->assertSuccessful()->assertInertia(fn (Assert $page): Assert => $page
        ->where('categoryFilter', null)
        ->where('total', 300.75)
        ->has('expenseGroups', 2)
        ->where('expenseGroups.0.label', 'Thu, 01 January 2026')
        ->where('expenseGroups.1.label', 'Wed, 31 December 2025')
        );
});

it('keeps the default current month view and all its expenses', function () {
    Expense::factory()->create(['date' => '2026-09-01', 'amount' => 20.25]);
    Expense::factory()->create(['date' => '2026-09-10', 'amount' => 30.50]);
    Expense::factory()->create(['date' => '2026-08-31', 'amount' => 900]);

    $this->get(route('expenses.index'))->assertSuccessful()->assertInertia(fn (Assert $page): Assert => $page
        ->where('month', '2026-09')
        ->where('monthLabel', 'September 2026')
        ->where('isCurrentMonth', true)
        ->where('isDateRange', false)
        ->where('categoryFilter', null)
        ->where('total', 50.75)
        ->has('expenseGroups', 2)
    );
});

it('sums the original amounts before rounding the filtered total', function () {
    Expense::factory()->create(['date' => '2026-09-01', 'amount' => 10.004]);
    Expense::factory()->create(['date' => '2026-09-02', 'amount' => 20.004]);

    $this->get(route('expenses.index', ['start_date' => '2026-09-01', 'end_date' => '2026-09-02']))
        ->assertSuccessful()->assertInertia(fn (Assert $page): Assert => $page
        ->where('total', 30.01)
        ->has('expenseGroups', 2)
        );
});

it('includes date objects throughout the last day of a report filter', function () {
    Expense::factory()->create(['date' => Date::parse('2026-09-10 00:00:00'), 'amount' => 100]);
    Expense::factory()->create(['date' => Date::parse('2026-09-10 23:59:59'), 'amount' => 200]);
    Expense::factory()->create(['date' => Date::parse('2026-09-11 00:00:00'), 'amount' => 900]);

    $this->get(route('expenses.index', ['start_date' => '2026-09-01', 'end_date' => '2026-09-10']))
        ->assertSuccessful()->assertInertia(fn (Assert $page): Assert => $page
        ->where('total', 300)
        ->has('expenseGroups', 1)
        ->has('expenseGroups.0.expenses', 2)
        );
});

it('returns zero for a valid filter without matching expenses', function () {
    $category = Category::factory()->create();
    Expense::factory()->create(['date' => '2026-09-01', 'amount' => 500]);

    $this->get(route('expenses.index', ['category_id' => $category->id]))
        ->assertSuccessful()->assertInertia(fn (Assert $page): Assert => $page
        ->where('categoryFilter.id', $category->id)
        ->where('total', 0)
        ->has('expenseGroups', 0)
        );
});

it('rejects invalid report filters', function (array $filters, string $field) {
    $this->getJson(route('expenses.index', $filters))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'invalid month' => [['month' => '2026-13'], 'month'],
    'invalid month format' => [['month' => '2026-9'], 'month'],
    'month array' => [['month' => ['2026-09']], 'month'],
    'invalid start date' => [['start_date' => '2026-02-30', 'end_date' => '2026-03-31'], 'start_date'],
    'invalid end date' => [['start_date' => '2026-02-01', 'end_date' => '2026-02-30'], 'end_date'],
    'missing start date' => [['end_date' => '2026-04-01'], 'start_date'],
    'missing end date' => [['start_date' => '2026-04-01'], 'end_date'],
    'reversed dates' => [['start_date' => '2026-04-30', 'end_date' => '2026-04-01'], 'end_date'],
    'mixed month and range' => [['month' => '2026-04', 'start_date' => '2026-04-01', 'end_date' => '2026-04-30'], 'month'],
    'missing category' => [['category_id' => 999999], 'category_id'],
    'invalid category' => [['category_id' => 'travel'], 'category_id'],
    'category array' => [['category_id' => [1]], 'category_id'],
]);
