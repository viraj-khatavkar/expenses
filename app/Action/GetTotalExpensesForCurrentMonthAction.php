<?php

namespace App\Action;

use App\Models\Expense;
use Illuminate\Support\Facades\Date;

final readonly class GetTotalExpensesForCurrentMonthAction
{
    public function handle()
    {
        return Expense::query()
            ->where('date', '>=', Date::now()->startOfMonth()->format('Y-m-d'))
            ->sum('amount');
    }
}
