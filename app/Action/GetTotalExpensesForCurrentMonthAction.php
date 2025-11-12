<?php

namespace App\Action;

use App\Models\Expense;
use Illuminate\Support\Facades\Date;

class GetTotalExpensesForCurrentMonthAction
{
    public function handle()
    {
        //        dd(Date::now()->startOfMonth()->format('Y-m-d'));

        return Expense::query()
            ->where('date', '>=', Date::now()->startOfMonth()->format('Y-m-d'))
            ->sum('amount');
    }
}
