<?php

namespace App\Action;

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

use function app;

final readonly class GetDefaultExpenseListAction
{
    /**
     * Create a new class instance.
     */
    public function handle()
    {
        return app(GetExpensesAction::class)
            ->handle(Date::now()->subMonth()->startOfMonth(), Date::now()->endOfMonth())
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
