<?php

namespace App\Action;

use Carbon\Carbon;

use function app;

final readonly class GetIncomeBySourceForFinancialYearAction
{
    /**
     * @return list<array{source: string, total: float, percentage: float}>
     */
    public function handle(Carbon $startDate, Carbon $endDate): array
    {
        $incomes = app(GetIncomesAction::class)
            ->handle($startDate, $endDate)
            ->with('source')
            ->get();

        $grandTotal = $incomes->sum('amount');

        return $incomes
            ->groupBy('source_id')
            ->map(fn ($group) => [
                'source' => $group->first()->source->name,
                'total' => $group->sum('amount'),
                'percentage' => $grandTotal > 0 ? ($group->sum('amount') / $grandTotal) * 100 : 0,
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }
}
