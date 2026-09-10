<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

it('removes the spending flag without changing expense records and restores a false default on rollback', function () {
    $migration = require database_path('migrations/2026_09_10_183401_drop_is_one_time_from_expenses_table.php');
    $migration->down();

    $expenses = Expense::factory()->count(3)->sequence(
        ['amount' => 250.50, 'is_one_time' => false, 'note' => 'Groceries'],
        ['amount' => 75000, 'is_one_time' => true, 'note' => 'Hospital'],
        ['amount' => 1200, 'is_one_time' => true, 'note' => 'Deleted expense'],
    )->create();
    $expenses->last()->delete();

    $before = Expense::withTrashed()->orderBy('id')->get()
        ->map(fn (Expense $expense): array => Arr::except($expense->getAttributes(), ['is_one_time']))
        ->all();

    $migration->up();

    expect(Schema::hasColumn('expenses', 'is_one_time'))->toBeFalse()
        ->and(Expense::withTrashed()->orderBy('id')->get()->map->getAttributes()->all())->toBe($before)
        ->and((float) Expense::query()->sum('amount'))->toBe(75250.50);

    $migration->down();

    expect(Schema::hasColumn('expenses', 'is_one_time'))->toBeTrue()
        ->and(Expense::withTrashed()->where('is_one_time', true)->count())->toBe(0)
        ->and(Expense::withTrashed()->orderBy('id')->get()
            ->map(fn (Expense $expense): array => Arr::except($expense->getAttributes(), ['is_one_time']))
            ->all())->toBe($before);

    $newExpense = Expense::factory()->create()->fresh();

    expect((bool) $newExpense->getAttribute('is_one_time'))->toBeFalse();

    $migration->up();
});

it('accepts expense changes without storing an obsolete spending flag', function () {
    $this->actingAs(User::factory()->create());
    $category = Category::factory()->create();

    $this->post(route('expenses.store'), [
        'category_id' => $category->id,
        'amount' => 75000,
        'date' => Date::today()->toDateString(),
        'note' => 'Hospital',
        'is_one_time' => true,
    ])->assertRedirect(route('expenses.index'))->assertSessionHasNoErrors();

    $expense = Expense::query()->sole();

    expect((float) $expense->amount)->toBe(75000.0)
        ->and($expense->getAttributes())->not->toHaveKey('is_one_time');

    $this->put(route('expenses.update', $expense), [
        'category_id' => $category->id,
        'amount' => 75000,
        'date' => $expense->date->toDateString(),
        'note' => 'Updated hospital note',
        'is_one_time' => false,
    ])->assertRedirect(route('expenses.index'))->assertSessionHasNoErrors();

    expect((float) $expense->fresh()->amount)->toBe(75000.0)
        ->and($expense->fresh()->note)->toBe('Updated hospital note');
});

it('includes all expenses in the home totals without an adjusted savings amount', function () {
    $this->actingAs(User::factory()->create());
    Income::factory()->create(['amount' => 100000, 'date' => Date::today()]);
    Expense::factory()->count(2)->sequence(
        ['amount' => 55000],
        ['amount' => 75000],
    )->create(['date' => Date::today()]);

    $this->get('/')->assertSuccessful()->assertInertia(fn (Assert $page): Assert => $page
        ->component('Home')
        ->where('thisYearTotal', fn (int|float $total): bool => (float) $total === 130000.0)
        ->where('thisYearIncome', fn (int|float $total): bool => (float) $total === 100000.0)
        ->missing('thisYearOneTimeTotal')
    );
});
