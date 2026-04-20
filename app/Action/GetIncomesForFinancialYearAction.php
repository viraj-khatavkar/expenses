<?php

namespace App\Action;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;

use function app;

final readonly class GetIncomesForFinancialYearAction
{
    public function handle(Carbon $startDate, Carbon $endDate)
    {
        return app(GetIncomesAction::class)
            ->handle($startDate, $endDate)
            ->with('source')
            ->orderByDesc('date')
            ->get()
            ->groupBy(fn (Income $income) => Date::parse($income->date)->format('Y-m'))
            ->sortKeysDesc()
            ->map(fn (Collection $incomes, string $month) => [
                'month' => Date::parse($month)->format('F Y'),
                'total' => $incomes->sum('amount'),
                'incomes' => $incomes->values(),
            ])
            ->values();
    }
}
