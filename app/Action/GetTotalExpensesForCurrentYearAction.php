<?php

namespace App\Action;

use Illuminate\Support\Facades\Date;

use function app;

final readonly class GetTotalExpensesForCurrentYearAction
{
    public function handle()
    {
        return app(GetExpensesAction::class)
            ->handle(
                Date::today()->startOfYear(),
                Date::today()->endOfYear()
            )
            ->sum('amount');
    }
}
