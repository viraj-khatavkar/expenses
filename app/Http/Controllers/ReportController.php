<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;

use function inertia;

class ReportController extends Controller
{
    public function __invoke()
    {
        $now = Date::today();
        $fyStart = $now->month >= 4
            ? Carbon::create($now->year, 4, 1)
            : Carbon::create($now->year - 1, 4, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();

        $fyLabel = 'FY '.$fyStart->year.'-'.substr((string) $fyEnd->year, 2);

        $expenses = Expense::with('category')
            ->where('date', '>=', $fyStart->format('Y-m-d'))
            ->where('date', '<=', $fyEnd->format('Y-m-d'))
            ->get();

        $total = $expenses->sum('amount');
        $transactionCount = $expenses->count();

        if ($transactionCount === 0) {
            return inertia('Reports/Index', [
                'fyLabel' => $fyLabel,
                'empty' => true,
            ]);
        }

        // --- Overview (immediate) ---
        $monthlyTotals = $expenses
            ->groupBy(fn (Expense $e) => Date::parse($e->date)->format('Y-m'))
            ->sortKeys()
            ->map(fn ($exps) => $exps->sum('amount'));

        $monthCount = $monthlyTotals->count();
        $avgMonthly = round($total / $monthCount, 2);

        $spendingDays = $expenses->groupBy(fn (Expense $e) => $e->date)->count();
        $firstExpenseDate = $expenses->min('date');
        $lastExpenseDate = $expenses->max('date');
        $totalDaysInRange = max(1, Date::parse($firstExpenseDate)->diffInDays(Date::parse($lastExpenseDate)) + 1);

        $trend = null;
        if ($monthCount >= 4) {
            $half = (int) floor($monthCount / 2);
            $firstHalfAvg = $monthlyTotals->take($half)->avg();
            $secondHalfAvg = $monthlyTotals->skip($half)->avg();
            if ($firstHalfAvg > 0) {
                $trendPercent = round((($secondHalfAvg - $firstHalfAvg) / $firstHalfAvg) * 100);
                if (abs($trendPercent) >= 10) {
                    $trend = $trendPercent;
                }
            }
        }

        return inertia('Reports/Index', [
            'fyLabel' => $fyLabel,
            'empty' => false,
            'overview' => [
                'total' => $total,
                'transactionCount' => $transactionCount,
                'monthCount' => $monthCount,
                'avgMonthly' => $avgMonthly,
                'projectedYearly' => round($avgMonthly * 12, 2),
                'avgPerTransaction' => round($total / $transactionCount, 2),
                'spendingDays' => $spendingDays,
                'totalDaysInRange' => $totalDaysInRange,
                'trend' => $trend,
            ],
            'insights' => Inertia::defer(fn () => $this->computeInsights($expenses, $total, $monthCount, $monthlyTotals, $spendingDays)),
        ]);
    }

    private function computeInsights($expenses, $total, $monthCount, $monthlyTotals, $spendingDays): array
    {
        // --- Spike Month ---
        $spikeMonth = null;
        if ($monthCount >= 3) {
            $maxMonthKey = $monthlyTotals->sort()->keys()->last();
            $maxTotal = $monthlyTotals[$maxMonthKey];
            $avgWithoutMax = $monthlyTotals->except($maxMonthKey)->avg();
            if ($avgWithoutMax > 0) {
                $spikeRatio = round($maxTotal / $avgWithoutMax, 1);
                if ($spikeRatio >= 1.5) {
                    $spikeExpenses = $expenses->filter(
                        fn (Expense $e) => Date::parse($e->date)->format('Y-m') === $maxMonthKey
                    );
                    $spikeDriver = $spikeExpenses
                        ->groupBy(fn (Expense $e) => $e->category->name)
                        ->map(fn ($exps) => $exps->sum('amount'))
                        ->sortDesc()
                        ->keys()
                        ->first();

                    $spikeMonth = [
                        'month' => Date::parse($maxMonthKey)->format('F'),
                        'total' => $maxTotal,
                        'ratio' => $spikeRatio,
                        'driver' => $spikeDriver,
                    ];
                }
            }
        }

        // --- Day of Week ---
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $byDayOfWeek = $expenses->groupBy(fn (Expense $e) => Date::parse($e->date)->format('l'));

        $spendingByDay = collect($dayOrder)->map(fn (string $day) => [
            'day' => substr($day, 0, 3),
            'total' => $byDayOfWeek->has($day) ? $byDayOfWeek[$day]->sum('amount') : 0,
            'count' => $byDayOfWeek->has($day) ? $byDayOfWeek[$day]->count() : 0,
        ])->values();

        $weekdayCount = collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
            ->sum(fn ($d) => $byDayOfWeek->has($d) ? $byDayOfWeek[$d]->count() : 0);
        $weekendCount = collect(['Saturday', 'Sunday'])
            ->sum(fn ($d) => $byDayOfWeek->has($d) ? $byDayOfWeek[$d]->count() : 0);
        $weekdayTotal = collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
            ->sum(fn ($d) => $byDayOfWeek->has($d) ? $byDayOfWeek[$d]->sum('amount') : 0);
        $weekendTotal = collect(['Saturday', 'Sunday'])
            ->sum(fn ($d) => $byDayOfWeek->has($d) ? $byDayOfWeek[$d]->sum('amount') : 0);

        // --- Most Frequent Category ---
        $categoryByCount = $expenses
            ->groupBy(fn (Expense $e) => $e->category->name)
            ->map(fn ($exps, $cat) => [
                'category' => $cat,
                'count' => $exps->count(),
                'total' => $exps->sum('amount'),
                'avgPerVisit' => round($exps->sum('amount') / $exps->count()),
                'monthsPresent' => $exps->groupBy(fn (Expense $e) => Date::parse($e->date)->format('Y-m'))->count(),
            ])
            ->sortByDesc('count');

        $topHabit = $categoryByCount->first();
        $topHabit['isRecurring'] = $topHabit['monthsPresent'] === $monthCount;
        $topHabit['projectedYearly'] = round(($topHabit['total'] / $monthCount) * 12);

        // --- Small Purchase Effect ---
        $smallPurchases = $expenses->filter(fn (Expense $e) => $e->amount < 2000);
        $smallCount = $smallPurchases->count();
        $smallTotal = $smallPurchases->sum('amount');
        $smallPercent = $total > 0 ? round(($smallTotal / $total) * 100) : 0;
        $smallPerWeek = $monthCount > 0 ? round($smallCount / ($monthCount * 4.33), 1) : 0;
        $showSmallPurchase = $smallPercent >= 15 && $smallCount >= 10;

        // --- Category Concentration ---
        $categoryTotals = $expenses
            ->groupBy(fn (Expense $e) => $e->category->name)
            ->map(fn ($exps) => $exps->sum('amount'))
            ->sortDesc();

        $top3Total = $categoryTotals->take(3)->sum();
        $top3Percent = $total > 0 ? round(($top3Total / $total) * 100) : 0;
        $remainingCount = max(0, $categoryTotals->count() - 3);

        // --- Most Expensive Single Day ---
        $byDate = $expenses->groupBy(fn (Expense $e) => $e->date);
        $biggestDay = $byDate->map(fn ($exps) => [
            'total' => $exps->sum('amount'),
            'count' => $exps->count(),
            'categories' => $exps->pluck('category.name')->unique()->values(),
        ])->sortByDesc('total');

        $biggestDayDate = $biggestDay->keys()->first();
        $biggestDayData = $biggestDay->first();
        $avgDailySpend = round($total / $spendingDays);

        return [
            'spikeMonth' => $spikeMonth,
            'spendingByDay' => $spendingByDay,
            'weekendPercent' => $total > 0 ? round(($weekendTotal / $total) * 100) : 0,
            'weekdayAvg' => $weekdayCount > 0 ? round($weekdayTotal / $weekdayCount) : 0,
            'weekendAvg' => $weekendCount > 0 ? round($weekendTotal / $weekendCount) : 0,
            'topHabit' => $topHabit,
            'smallPurchase' => $showSmallPurchase ? [
                'count' => $smallCount,
                'total' => $smallTotal,
                'percent' => $smallPercent,
                'perWeek' => $smallPerWeek,
            ] : null,
            'concentration' => [
                'names' => $categoryTotals->take(3)->keys()->toArray(),
                'percent' => $top3Percent,
                'remainingCount' => $remainingCount,
                'remainingTotal' => $total - $top3Total,
            ],
            'biggestDay' => [
                'date' => Date::parse($biggestDayDate)->format('d M Y'),
                'total' => $biggestDayData['total'],
                'count' => $biggestDayData['count'],
                'categories' => $biggestDayData['categories'],
                'multiple' => $avgDailySpend > 0 ? round($biggestDayData['total'] / $avgDailySpend, 1) : 0,
            ],
        ];
    }
}
