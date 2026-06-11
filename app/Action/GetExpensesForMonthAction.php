<?php

namespace App\Action;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

use function app;

final readonly class GetExpensesForMonthAction
{
    /**
     * Group a month's expenses by day, newest first, with a total per day.
     *
     * @return Collection<int, array{label: string, total: float, expenses: Collection<int, Expense>}>
     */
    public function handle(Carbon $monthStart): Collection
    {
        return app(GetExpensesAction::class)
            ->handle($monthStart->copy()->startOfMonth(), $monthStart->copy()->endOfMonth())
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->with('category')
            ->get()
            ->groupBy(fn (Expense $expense) => Date::parse($expense->date)->format('Y-m-d'))
            ->map(function (Collection $expenses, string $dateString) {
                $date = Date::parse($dateString);

                $label = match (true) {
                    $date->isToday() => 'Today',
                    $date->isYesterday() => 'Yesterday',
                    default => $date->format('D, d F'),
                };

                return [
                    'label' => $label,
                    'total' => (float) $expenses->sum('amount'),
                    'expenses' => $expenses->values(),
                ];
            })
            ->values();
    }
}
