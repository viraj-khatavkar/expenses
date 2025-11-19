<?php

namespace App\Action;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;

use function app;

final readonly class GetExpensesGroupedByMonthForYear
{
    public function handle(Carbon $date)
    {
        return app(GetExpensesAction::class)
            ->handle($date->startOfYear(), $date->endOfYear())
            ->orderByDesc('date')
            ->get()
            ->groupBy(fn (Expense $expense) => Date::parse($expense->date)->format('Y-m'))
            ->map(fn (Collection $expenses, string $month) => [
                'month' => Date::parse($month)->format('F'),
                'total' => $expenses->sum('amount'),
            ])
            ->values();
    }
}
