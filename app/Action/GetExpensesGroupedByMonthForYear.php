<?php

namespace App\Action;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;

use function app;

final readonly class GetExpensesGroupedByMonthForYear
{
    public function handle(Carbon $startDate, Carbon $endDate)
    {
        return app(GetExpensesAction::class)
            ->handle($startDate, $endDate)
            ->with('category')
            ->get()
            ->groupBy(fn (Expense $expense) => Date::parse($expense->date)->format('Y-m'))
            ->sortKeys()
            ->map(fn (Collection $expenses, string $month) => [
                'month' => Date::parse($month)->format('F'),
                'total' => $expenses->sum('amount'),
                'categories' => $expenses
                    ->groupBy(fn (Expense $expense) => $expense->category->name)
                    ->map(fn (Collection $categoryExpenses, string $category) => [
                        'category' => $category,
                        'total' => $categoryExpenses->sum('amount'),
                    ])
                    ->sortByDesc('total')
                    ->values(),
            ])
            ->values();
    }
}
