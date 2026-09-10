<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Date::parse('2026-09-10 12:00:00'));
    $this->actingAs(User::factory()->create());
});

it('uses recorded totals through today and averages only completed financial year months', function () {
    Expense::factory()->create(['amount' => 1000, 'date' => '2026-04-01']);
    Expense::factory()->create(['amount' => 2000, 'date' => '2026-08-31']);
    Expense::factory()->create(['amount' => 900, 'date' => '2026-09-10']);
    Expense::factory()->create(['amount' => 80000, 'date' => '2026-09-11']);
    Expense::factory()->create(['amount' => 90000, 'date' => '2026-10-01']);
    Income::factory()->create(['amount' => 10000, 'date' => '2026-09-10']);
    Income::factory()->create(['amount' => 50000, 'date' => '2026-09-11']);
    Income::factory()->create(['amount' => 60000, 'date' => '2026-10-01']);

    $props = $this->get('/reports')->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page->component('Reports/Index'))
        ->inertiaProps();

    expect($props['empty'])->toBeFalse()
        ->and($props['period'])->toMatchArray([
            'startDate' => '2026-04-01',
            'endDate' => '2026-09-10',
            'isCurrent' => true,
            'completedMonths' => 5,
            'averageLabel' => 'Based on 5 completed months',
        ])
        ->and($props['cashflow'])->toEqual([
            'income' => 10000,
            'spent' => 3900,
            'net' => 6100,
            'netRate' => 61,
            'avgMonthlySpend' => 600,
        ])
        ->and($props['monthly'])->toHaveCount(12)
        ->and($props['monthly'][5])->toMatchArray([
            'monthKey' => '2026-09', 'spent' => 900, 'income' => 10000,
            'isCurrent' => true, 'isFuture' => false, 'hasExpenses' => true, 'hasIncome' => true,
            'expensesUrl' => route('expenses.index', ['start_date' => '2026-09-01', 'end_date' => '2026-09-10']),
        ])
        ->and($props['monthly'][6])->toMatchArray([
            'spent' => 0, 'income' => 0, 'isFuture' => true, 'hasExpenses' => false, 'hasIncome' => false,
        ])
        ->and($props['biggestExpenses'])->toHaveCount(3)
        ->and(collect($props['categoryTotals'])->sum('total'))->toEqual(3900);
});

it('includes date objects throughout the report cutoff day and excludes the following day', function () {
    Expense::factory()->create(['amount' => 1000, 'date' => Date::today()]);
    Expense::factory()->create(['amount' => 2000, 'date' => Date::today()->setTime(23, 59, 59)]);
    Expense::factory()->create(['amount' => 90000, 'date' => Date::tomorrow()]);
    Income::factory()->create(['amount' => 4000, 'date' => Date::today()]);
    Income::factory()->create(['amount' => 5000, 'date' => Date::today()->setTime(23, 59, 59)]);
    Income::factory()->create(['amount' => 90000, 'date' => Date::tomorrow()]);
    Expense::factory()->create(['amount' => 500, 'date' => Date::today()->subYear()->setTime(23, 59, 59)]);
    Expense::factory()->create(['amount' => 90000, 'date' => Date::tomorrow()->subYear()]);

    $props = $this->get('/reports')->assertSuccessful()->inertiaProps();

    expect($props['empty'])->toBeFalse()
        ->and($props['cashflow']['spent'])->toEqual(3000)
        ->and($props['cashflow']['income'])->toEqual(9000)
        ->and($props['monthly'][5]['spent'])->toEqual(3000)
        ->and($props['monthly'][5]['income'])->toEqual(9000)
        ->and($props['annualComparison']['previousTotal'])->toEqual(500)
        ->and($props['biggestExpenses'])->toHaveCount(2);
});

it('compares the same elapsed financial year dates and shows category changes', function () {
    $category = Category::factory()->create(['name' => 'Food']);
    Expense::factory()->for($category)->create(['amount' => 4000, 'date' => '2026-09-10']);
    Expense::factory()->for($category)->create(['amount' => 1000, 'date' => '2025-04-01']);
    Expense::factory()->for($category)->create(['amount' => 2000, 'date' => '2025-09-10']);
    Expense::factory()->for($category)->create(['amount' => 80000, 'date' => '2025-09-11']);
    Expense::factory()->for($category)->create(['amount' => 90000, 'date' => '2025-03-31']);

    $props = $this->get('/reports')->assertSuccessful()->inertiaProps();

    expect($props['annualComparison'])->toEqual([
        'currentLabel' => '1 Apr 2026 – 10 Sep 2026',
        'previousLabel' => '1 Apr 2025 – 10 Sep 2025',
        'currentTotal' => 4000, 'previousTotal' => 3000, 'difference' => 1000,
        'percentageChange' => 33.3, 'currentHasExpenses' => true, 'previousHasExpenses' => true,
    ])->and($props['categoryTotals'][0])->toEqual([
        'id' => $category->id, 'name' => 'Food', 'total' => 4000, 'share' => 100,
        'previousTotal' => 3000, 'change' => 1000, 'changePercent' => 33.3,
        'expensesUrl' => route('expenses.index', [
            'category_id' => $category->id, 'start_date' => '2026-04-01', 'end_date' => '2026-09-10',
        ]),
    ]);
});

it('has no monthly comparison or average during the first financial year month', function () {
    $this->travelTo(Date::parse('2026-04-15 12:00:00'));
    Expense::factory()->create(['amount' => 1500, 'date' => '2026-04-15']);

    $props = $this->get('/reports')->assertSuccessful()->inertiaProps();

    expect($props['period']['completedMonths'])->toBe(0)
        ->and($props['period']['averageLabel'])->toBe('No completed months yet')
        ->and($props['cashflow']['avgMonthlySpend'])->toBeNull()
        ->and($props['monthlyComparison'])->toBeNull()
        ->and($props['cashflow']['spent'])->toEqual(1500);
});

it('does not report a zero monthly average before expense records exist in completed months', function () {
    Expense::factory()->create(['amount' => 900, 'date' => '2026-09-10']);

    $props = $this->get('/reports')->assertSuccessful()->inertiaProps();

    expect($props['cashflow']['avgMonthlySpend'])->toBeNull()
        ->and($props['period']['averageLabel'])->toBe('No expenses recorded in completed months')
        ->and($props['monthlyComparison']['currentHasExpenses'])->toBeFalse()
        ->and($props['monthlyComparison']['previousHasExpenses'])->toBeFalse()
        ->and($props['monthlyComparison']['percentageChange'])->toBeNull();
});

it('compares April with March across the financial year boundary', function () {
    $this->travelTo(Date::parse('2026-05-01 12:00:00'));
    $category = Category::factory()->create(['name' => 'Travel']);
    Expense::factory()->for($category)->create(['amount' => 2000, 'date' => '2026-03-31']);
    Expense::factory()->for($category)->create(['amount' => 6000, 'date' => '2026-04-30']);
    Expense::factory()->for($category)->create(['amount' => 9000, 'date' => '2026-05-01']);

    $props = $this->get('/reports')->assertSuccessful()->inertiaProps();

    expect($props['monthlyComparison'])->toMatchArray([
        'currentLabel' => 'Apr 2026', 'previousLabel' => 'Mar 2026',
        'currentTotal' => 6000, 'previousTotal' => 2000, 'difference' => 4000,
        'percentageChange' => 200,
        'expensesUrl' => route('expenses.index', ['month' => '2026-04']),
    ])->and($props['monthlyComparison']['drivers'][0])->toEqual([
        'categoryId' => $category->id, 'name' => 'Travel', 'currentTotal' => 6000,
        'previousTotal' => 2000, 'difference' => 4000,
        'expensesUrl' => route('expenses.index', ['month' => '2026-04', 'category_id' => $category->id]),
    ])->and($props['cashflow']['spent'])->toEqual(15000)
        ->and($props['cashflow']['avgMonthlySpend'])->toEqual(6000);
});

it('uses all twelve months for a past financial year and compares its final completed months', function () {
    Expense::factory()->create(['amount' => 6000, 'date' => '2025-04-01']);
    Expense::factory()->create(['amount' => 2000, 'date' => '2026-02-28']);
    Expense::factory()->create(['amount' => 4000, 'date' => '2026-03-31']);
    Expense::factory()->create(['amount' => 90000, 'date' => '2026-04-01']);
    Expense::factory()->create(['amount' => 3000, 'date' => '2025-03-31']);

    $props = $this->get('/reports?fy=2025')->assertSuccessful()->inertiaProps();

    expect($props['period'])->toMatchArray([
        'startDate' => '2025-04-01', 'endDate' => '2026-03-31',
        'isCurrent' => false, 'completedMonths' => 12,
    ])->and($props['cashflow']['spent'])->toEqual(12000)
        ->and($props['cashflow']['avgMonthlySpend'])->toEqual(1000)
        ->and($props['monthlyComparison'])->toMatchArray([
            'currentLabel' => 'Mar 2026', 'previousLabel' => 'Feb 2026',
            'currentTotal' => 4000, 'previousTotal' => 2000, 'difference' => 2000,
        ])
        ->and($props['annualComparison']['previousTotal'])->toEqual(3000)
        ->and($props['monthly'][11]['isFuture'])->toBeFalse()
        ->and($props['monthly'][11]['isCurrent'])->toBeFalse();
});

it('keeps a leap day cutoff within February of the previous year', function () {
    $this->travelTo(Date::parse('2024-02-29 12:00:00'));
    Expense::factory()->create(['amount' => 400, 'date' => '2024-02-29']);
    Expense::factory()->create(['amount' => 100, 'date' => '2023-02-28']);
    Expense::factory()->create(['amount' => 90000, 'date' => '2023-03-01']);

    $props = $this->get('/reports')->assertSuccessful()->inertiaProps();

    expect($props['period']['endDate'])->toBe('2024-02-29')
        ->and($props['annualComparison'])->toMatchArray([
            'currentLabel' => '1 Apr 2023 – 29 Feb 2024',
            'previousLabel' => '1 Apr 2022 – 28 Feb 2023',
            'currentTotal' => 400, 'previousTotal' => 100, 'percentageChange' => 300,
        ]);
});

it('keeps all categories including categories found only in the previous comparison period', function () {
    foreach (range(1, 10) as $number) {
        $category = Category::factory()->create(['name' => 'Category '.$number]);
        Expense::factory()->for($category)->create(['amount' => $number * 100, 'date' => '2026-08-01']);
    }
    $previousCategory = Category::factory()->create(['name' => 'Previous category']);
    Expense::factory()->for($previousCategory)->create(['amount' => 2000, 'date' => '2025-08-01']);

    $props = $this->get('/reports')->assertSuccessful()->inertiaProps();

    expect($props['categoryTotals'])->toHaveCount(11)
        ->and($props['categoryTotals'][0]['total'])->toEqual(1000)
        ->and(collect($props['categoryTotals'])->sum('total'))->toEqual(5500)
        ->and($props['categoryTotals'][10])->toMatchArray([
            'id' => $previousCategory->id, 'total' => 0, 'share' => 0,
            'previousTotal' => 2000, 'change' => -2000, 'changePercent' => -100,
        ])
        ->and($props['biggestExpenses'])->toHaveCount(8)
        ->and($props['biggestExpenses'][0]['url'])->toBe(route('expenses.edit', $props['biggestExpenses'][0]['id']));
});

it('lists the five largest category changes by amount including decreases', function () {
    foreach (range(1, 6) as $number) {
        $category = Category::factory()->create(['name' => 'Category '.$number]);
        Expense::factory()->for($category)->create(['amount' => 1000, 'date' => '2026-07-01']);
        Expense::factory()->for($category)->create(['amount' => 1000 + $number * 100, 'date' => '2026-08-01']);
    }
    $decreased = Category::factory()->create(['name' => 'Decreased']);
    Expense::factory()->for($decreased)->create(['amount' => 5000, 'date' => '2026-07-01']);
    $unchanged = Category::factory()->create(['name' => 'Unchanged']);
    Expense::factory()->for($unchanged)->create(['amount' => 400, 'date' => '2026-07-01']);
    Expense::factory()->for($unchanged)->create(['amount' => 400, 'date' => '2026-08-01']);

    $props = $this->get('/reports')->assertSuccessful()->inertiaProps();

    expect($props['monthlyComparison']['drivers'])->toHaveCount(5)
        ->and(array_column($props['monthlyComparison']['drivers'], 'difference'))->toEqual([-5000, 600, 500, 400, 300])
        ->and($props['monthlyComparison']['drivers'][0]['name'])->toBe('Decreased');
});

it('preserves record availability when comparison amounts have a zero denominator', function (bool $hasPreviousRecord) {
    Expense::factory()->create(['amount' => 100, 'date' => '2026-08-01']);
    if ($hasPreviousRecord) {
        Expense::factory()->create(['amount' => 0, 'date' => '2026-07-01']);
        Expense::factory()->create(['amount' => 0, 'date' => '2025-08-01']);
    }

    $props = $this->get('/reports')->assertSuccessful()->inertiaProps();

    expect($props['monthlyComparison']['previousHasExpenses'])->toBe($hasPreviousRecord)
        ->and($props['monthlyComparison']['percentageChange'])->toBeNull()
        ->and($props['annualComparison']['previousHasExpenses'])->toBe($hasPreviousRecord)
        ->and($props['annualComparison']['percentageChange'])->toBeNull()
        ->and($props['cashflow']['netRate'])->toBeNull();
})->with(['no previous records' => false, 'recorded zero' => true]);

it('returns period details and no recorded totals when entries are only future dated', function () {
    Expense::factory()->create(['amount' => 1000, 'date' => '2027-05-01']);
    Income::factory()->create(['amount' => 2000, 'date' => '2027-05-01']);

    $props = $this->get('/reports')->assertSuccessful()->inertiaProps();

    expect($props['empty'])->toBeTrue()
        ->and($props['period']['endDate'])->toBe('2026-09-10')
        ->and($props['cashflow']['spent'])->toEqual(0)
        ->and($props['cashflow']['income'])->toEqual(0)
        ->and($props['cashflow']['avgMonthlySpend'])->toBeNull()
        ->and($props['annualComparison']['percentageChange'])->toBeNull()
        ->and($props['availableFys'])->toEqual([['year' => 2026, 'label' => 'FY 2026-27']]);
});
