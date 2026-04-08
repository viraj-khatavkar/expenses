<?php

namespace App\Action;

use Carbon\Carbon;

use function app;

final readonly class GetTotalExpensesForCurrentYearAction
{
    public function handle(Carbon $startDate, Carbon $endDate)
    {
        return app(GetExpensesAction::class)
            ->handle($startDate, $endDate)
            ->sum('amount');
    }
}
