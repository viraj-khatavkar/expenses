<?php

namespace App\Action;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

final readonly class GetDefaultExpenseListAction
{
    /**
     * Create a new class instance.
     */
    public function handle()
    {
        return Expense::query()
            ->where('date', '>', Date::now()->subMonth()->startOfMonth()->format('Y-m-d'))
            ->orderByDesc('date')
            ->with('category')
            ->get()
            ->groupBy('date')
            ->mapWithKeys(function ($expense, $date) {
                $date = new Carbon($date);

                if ($date->eq(Date::today())) {
                    return ['Today' => $expense];
                }

                if ($date->eq(Date::yesterday())) {
                    return ['Yesterday' => $expense];
                }

                return [$date->copy()->format('D, d F') => $expense];
            });
    }
}
