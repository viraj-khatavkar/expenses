<?php

namespace App\Action;

use App\Models\InvestmentMonth;
use App\Models\InvestmentTransaction;
use App\Models\User;
use Illuminate\Support\Collection;

final class GetInvestmentYearAction
{
    /**
     * @return array{months: Collection, selected: array, yearNetPaise: int, yearTargetPaise: ?int}
     */
    public function handle(User $user, string $period): array
    {
        $year = substr($period, 0, 4);
        $flows = InvestmentTransaction::forUser($user)
            ->whereBetween('period', [$year.'-01', $year.'-12'])
            ->select('period')
            ->selectRaw("SUM(CASE WHEN direction = 'deposit' THEN amount_paise ELSE 0 END) AS deposited")
            ->selectRaw("SUM(CASE WHEN direction = 'redemption' THEN amount_paise ELSE 0 END) AS redeemed")
            ->groupBy('period')->get()->keyBy('period');

        $plans = InvestmentMonth::whereBelongsTo($user)->where('year', $year)
            ->whereNotNull('planned')->pluck('planned', 'month');

        $months = collect(range(1, 12))->map(function (int $month) use ($year, $flows, $plans): array {
            $key = sprintf('%s-%02d', $year, $month);
            $flow = $flows->get($key);
            $deposited = (int) ($flow?->deposited ?? 0);
            $redeemed = (int) ($flow?->redeemed ?? 0);

            return [
                'period' => $key,
                'depositedPaise' => $deposited,
                'redeemedPaise' => $redeemed,
                'netPaise' => $deposited - $redeemed,
                'targetPaise' => $plans->has($month) ? (int) round((float) $plans[$month] * 100) : null,
            ];
        });

        return [
            'selected' => $months->firstWhere('period', $period),
            'months' => $months->filter(fn (array $month): bool => $flows->has($month['period']) || $month['targetPaise'] !== null)
                ->reverse()->values(),
            'yearNetPaise' => $months->sum('netPaise'),
            'yearTargetPaise' => $plans->isEmpty() ? null : $months->sum('targetPaise'),
        ];
    }
}
