<?php

namespace App\Action;

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
            ->groupBy(fn ($expense) => Date::parse($expense->date)->format('Y-m-d'))
            ->mapWithKeys(function ($expenses, $dateStr) {
                $date = Date::parse($dateStr);

                if ($date->isToday()) {
                    return ['Today' => $expenses];
                }

                if ($date->isYesterday()) {
                    return ['Yesterday' => $expenses];
                }

                return [$date->format('D, d F') => $expenses];
            });
    }
}
