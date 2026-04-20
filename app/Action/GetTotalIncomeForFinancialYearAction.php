<?php

namespace App\Action;

use Carbon\Carbon;

use function app;

final readonly class GetTotalIncomeForFinancialYearAction
{
    public function handle(Carbon $startDate, Carbon $endDate)
    {
        return app(GetIncomesAction::class)
            ->handle($startDate, $endDate)
            ->sum('amount');
    }
}
