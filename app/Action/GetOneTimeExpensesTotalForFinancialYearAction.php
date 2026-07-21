<?php

namespace App\Action;

use Carbon\Carbon;

use function app;

final readonly class GetOneTimeExpensesTotalForFinancialYearAction
{
    public function handle(Carbon $startDate, Carbon $endDate)
    {
        return app(GetExpensesAction::class)
            ->handle($startDate, $endDate)
            ->where('is_one_time', true)
            ->sum('amount');
    }
}
