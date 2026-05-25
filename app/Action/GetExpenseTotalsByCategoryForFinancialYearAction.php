<?php

namespace App\Action;

use Carbon\Carbon;

use function app;

final readonly class GetExpenseTotalsByCategoryForFinancialYearAction
{
    /**
     * @return list<array{id: int, name: string, total: float}>
     */
    public function handle(Carbon $startDate, Carbon $endDate): array
    {
        return app(GetExpensesAction::class)
            ->handle($startDate, $endDate)
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($group) => [
                'id' => $group->first()->category_id,
                'name' => $group->first()->category->name,
                'total' => $group->sum('amount'),
            ])
            ->sortBy('name')
            ->values()
            ->all();
    }
}
