<?php

namespace App\Action;

use App\Models\Expense;
use Carbon\Carbon;

final readonly class GetTotalExpensesForMonthAction
{
    public function handle(Carbon $monthStart): float
    {
        return (float) Expense::query()
            ->where('date', '>=', $monthStart->copy()->startOfMonth()->format('Y-m-d'))
            ->where('date', '<=', $monthStart->copy()->endOfMonth()->format('Y-m-d'))
            ->sum('amount');
    }
}
