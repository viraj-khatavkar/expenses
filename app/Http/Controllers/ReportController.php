<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Inertia\Response;

use function inertia;

class ReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $now = Date::today();
        $currentFyStart = $now->month >= 4 ? $now->year : $now->year - 1;

        $fy = (int) $request->input('fy', $currentFyStart);
        $fyStart = Carbon::create($fy, 4, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();

        $fyLabel = 'FY '.$fyStart->year.'-'.substr((string) $fyEnd->year, 2);

        $expenses = Expense::with('category')
            ->where('date', '>=', $fyStart->format('Y-m-d'))
            ->where('date', '<=', $fyEnd->format('Y-m-d'))
            ->get();

        $incomes = Income::query()
            ->where('date', '>=', $fyStart->format('Y-m-d'))
            ->where('date', '<=', $fyEnd->format('Y-m-d'))
            ->get();

        $oneTimeExpenses = $expenses->filter(fn (Expense $expense) => $expense->is_one_time);
        $regularExpenses = $expenses->reject(fn (Expense $expense) => $expense->is_one_time);

        $spentTotal = (float) $expenses->sum('amount');
        $oneTimeTotal = (float) $oneTimeExpenses->sum('amount');
        $regularTotal = $spentTotal - $oneTimeTotal;
        $incomeTotal = (float) $incomes->sum('amount');

        if ($spentTotal === 0.0 && $incomeTotal === 0.0) {
            return inertia('Reports/Index', [
                'fy' => $fy,
                'fyLabel' => $fyLabel,
                'availableFys' => $this->availableFinancialYears($currentFyStart),
                'empty' => true,
            ]);
        }

        $elapsedMonths = $this->elapsedMonths($fyStart, $fyEnd, $now);
        $monthKeys = $this->financialYearMonthKeys($fyStart);

        return inertia('Reports/Index', [
            'fy' => $fy,
            'fyLabel' => $fyLabel,
            'availableFys' => $this->availableFinancialYears($currentFyStart),
            'empty' => false,
            'cashflow' => [
                'income' => $incomeTotal,
                'spent' => $spentTotal,
                'saved' => round($incomeTotal - $spentTotal, 2),
                'savingsRate' => $incomeTotal > 0
                    ? (int) round(($incomeTotal - $spentTotal) / $incomeTotal * 100)
                    : null,
                'avgMonthlySpend' => round($spentTotal / $elapsedMonths, 2),
                'regularSpent' => round($regularTotal, 2),
                'oneTimeSpent' => round($oneTimeTotal, 2),
                'oneTimeCount' => $oneTimeExpenses->count(),
                'coreSaved' => round($incomeTotal - $regularTotal, 2),
                'coreSavingsRate' => $incomeTotal > 0
                    ? (int) round(($incomeTotal - $regularTotal) / $incomeTotal * 100)
                    : null,
                'avgMonthlyRegularSpend' => round($regularTotal / $elapsedMonths, 2),
            ],
            'monthly' => $this->monthlyCashflow($monthKeys, $expenses, $incomes, $now),
            'categoryTrends' => $this->categoryTrends($monthKeys, $regularExpenses, $regularTotal, $elapsedMonths),
            'biggestExpenses' => $this->biggestExpenses($expenses),
        ]);
    }

    /**
     * The 12 month keys (Y-m) of the financial year, April through March.
     *
     * @return Collection<int, string>
     */
    private function financialYearMonthKeys(Carbon $fyStart): Collection
    {
        return collect(range(0, 11))
            ->map(fn (int $offset) => $fyStart->copy()->addMonths($offset)->format('Y-m'));
    }

    private function elapsedMonths(Carbon $fyStart, Carbon $fyEnd, Carbon $now): int
    {
        $lastVisibleMonth = $fyEnd->lessThan($now)
            ? $fyEnd->copy()->startOfMonth()
            : $now->copy()->startOfMonth();

        if ($lastVisibleMonth->lessThan($fyStart)) {
            return 1;
        }

        return (int) $fyStart->diffInMonths($lastVisibleMonth) + 1;
    }

    /**
     * @return array<int, array{monthKey: string, label: string, spent: float, income: float, net: float, isCurrent: bool, isFuture: bool}>
     */
    private function monthlyCashflow(Collection $monthKeys, Collection $expenses, Collection $incomes, Carbon $now): array
    {
        $spentByMonth = $expenses
            ->groupBy(fn (Expense $expense) => Date::parse($expense->date)->format('Y-m'))
            ->map(fn (Collection $monthExpenses) => (float) $monthExpenses->sum('amount'));

        $incomeByMonth = $incomes
            ->groupBy(fn (Income $income) => Date::parse($income->date)->format('Y-m'))
            ->map(fn (Collection $monthIncomes) => (float) $monthIncomes->sum('amount'));

        $currentKey = $now->format('Y-m');

        return $monthKeys
            ->map(function (string $key) use ($spentByMonth, $incomeByMonth, $currentKey) {
                $spent = $spentByMonth[$key] ?? 0.0;
                $income = $incomeByMonth[$key] ?? 0.0;

                return [
                    'monthKey' => $key,
                    'label' => Date::parse($key)->format('M'),
                    'spent' => $spent,
                    'income' => $income,
                    'net' => round($income - $spent, 2),
                    'isCurrent' => $key === $currentKey,
                    'isFuture' => $key > $currentKey,
                ];
            })
            ->all();
    }

    /**
     * Trends cover regular spending only, so one-time events don't drown the baseline.
     *
     * @return array<int, array{name: string, total: float, count: int, share: int, avgPerMonth: float, monthly: array<int, float>}>
     */
    private function categoryTrends(Collection $monthKeys, Collection $regularExpenses, float $regularTotal, int $elapsedMonths): array
    {
        return $regularExpenses
            ->groupBy(fn (Expense $expense) => $expense->category->name)
            ->map(function (Collection $items, string $name) use ($monthKeys, $regularTotal, $elapsedMonths) {
                $byMonth = $items
                    ->groupBy(fn (Expense $expense) => Date::parse($expense->date)->format('Y-m'))
                    ->map(fn (Collection $monthItems) => (float) $monthItems->sum('amount'));

                $total = (float) $items->sum('amount');

                return [
                    'name' => $name,
                    'total' => $total,
                    'count' => $items->count(),
                    'share' => $regularTotal > 0 ? (int) round($total / $regularTotal * 100) : 0,
                    'avgPerMonth' => round($total / $elapsedMonths, 2),
                    'monthly' => $monthKeys->map(fn (string $key) => $byMonth[$key] ?? 0.0)->all(),
                ];
            })
            ->sortByDesc('total')
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, date: string, category: string, note: string|null, amount: float, isOneTime: bool}>
     */
    private function biggestExpenses(Collection $expenses): array
    {
        return $expenses
            ->sortByDesc('amount')
            ->take(8)
            ->values()
            ->map(fn (Expense $expense) => [
                'id' => $expense->id,
                'date' => Date::parse($expense->date)->format('j M'),
                'category' => $expense->category->name,
                'note' => $expense->note,
                'amount' => (float) $expense->amount,
                'isOneTime' => $expense->is_one_time,
            ])
            ->all();
    }

    /**
     * Financial years from the earliest recorded expense or income up to the
     * current financial year, newest first.
     *
     * @return array<int, array{year: int, label: string}>
     */
    private function availableFinancialYears(int $currentFyStart): array
    {
        $earliest = collect([
            Expense::query()->min('date'),
            Income::query()->min('date'),
        ])->filter()->min();

        if ($earliest === null) {
            $earliestFyStart = $currentFyStart;
        } else {
            $earliestDate = Date::parse($earliest);
            $earliestFyStart = $earliestDate->month >= 4 ? $earliestDate->year : $earliestDate->year - 1;
        }

        return array_map(
            fn (int $year): array => [
                'year' => $year,
                'label' => 'FY '.$year.'-'.substr((string) ($year + 1), 2),
            ],
            range($currentFyStart, $earliestFyStart),
        );
    }
}
