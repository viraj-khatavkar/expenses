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
        $today = Date::today();
        $currentFyStart = $today->month >= 4 ? $today->year : $today->year - 1;
        $requestedFy = filter_var($request->input('fy', $currentFyStart), FILTER_VALIDATE_INT);
        $fy = $requestedFy !== false && $requestedFy >= 1900 && $requestedFy <= $currentFyStart
            ? $requestedFy
            : $currentFyStart;
        $fyStart = Carbon::create($fy, 4, 1)->startOfDay();
        $fyEnd = $fyStart->copy()->addYear()->subDay();
        $periodEnd = $fyEnd->lessThan($today) ? $fyEnd : $today;
        $previousStart = $fyStart->copy()->subYear();
        $previousEnd = $periodEnd->copy()->subYearNoOverflow();
        $completedMonths = $fyEnd->lessThan($today)
            ? 12
            : (int) $fyStart->diffInMonths($today->copy()->startOfMonth());

        $allExpenses = Expense::query()
            ->with('category:id,name')
            ->where('date', '>=', $previousStart->toDateString())
            ->where('date', '<', $periodEnd->copy()->addDay()->toDateString())
            ->get(['id', 'category_id', 'date', 'amount', 'note']);
        $expenses = $this->expensesBetween($allExpenses, $fyStart, $periodEnd);
        $previousExpenses = $this->expensesBetween($allExpenses, $previousStart, $previousEnd);
        $incomes = Income::query()
            ->where('date', '>=', $fyStart->toDateString())
            ->where('date', '<', $periodEnd->copy()->addDay()->toDateString())
            ->get(['id', 'date', 'amount']);
        $spentTotal = round((float) $expenses->sum('amount'), 2);
        $incomeTotal = round((float) $incomes->sum('amount'), 2);
        $completedExpenses = $expenses
            ->filter(fn (Expense $expense): bool => $expense->date->lessThan($fyStart->copy()->addMonths($completedMonths)));
        $averageLabel = $completedMonths === 0
            ? 'No completed months yet'
            : 'Based on '.$completedMonths.' completed '.($completedMonths === 1 ? 'month' : 'months');
        if ($completedMonths > 0 && $completedExpenses->isEmpty()) {
            $averageLabel = 'No expenses recorded in completed months';
        }

        return inertia('Reports/Index', [
            'fy' => $fy,
            'fyLabel' => 'FY '.$fy.'-'.substr((string) ($fy + 1), 2),
            'availableFys' => $this->availableFinancialYears($currentFyStart),
            'empty' => $expenses->isEmpty() && $incomes->isEmpty(),
            'period' => [
                'label' => $this->periodLabel($fyStart, $periodEnd),
                'startDate' => $fyStart->toDateString(),
                'endDate' => $periodEnd->toDateString(),
                'isCurrent' => $fy === $currentFyStart,
                'completedMonths' => $completedMonths,
                'averageLabel' => $averageLabel,
            ],
            'cashflow' => [
                'income' => $incomeTotal,
                'spent' => $spentTotal,
                'net' => round($incomeTotal - $spentTotal, 2),
                'netRate' => $incomeTotal > 0 ? round(($incomeTotal - $spentTotal) / $incomeTotal * 100, 1) : null,
                'avgMonthlySpend' => $completedMonths > 0 && $completedExpenses->isNotEmpty()
                    ? round($completedExpenses->sum('amount') / $completedMonths, 2)
                    : null,
            ],
            'monthly' => $this->monthlyCashflow($fyStart, $expenses, $incomes, $today),
            'monthlyComparison' => $completedMonths === 0
                ? null
                : $this->monthlyComparison($allExpenses, $fyStart->copy()->addMonths($completedMonths - 1)),
            'annualComparison' => $this->comparison(
                $expenses,
                $previousExpenses,
                $this->periodLabel($fyStart, $periodEnd),
                $this->periodLabel($previousStart, $previousEnd),
            ),
            'categoryTotals' => $this->categoryTotals($expenses, $previousExpenses, $fyStart, $periodEnd),
            'biggestExpenses' => $this->biggestExpenses($expenses),
        ]);
    }

    /**
     * @param  Collection<int, Expense>  $expenses
     * @return Collection<int, Expense>
     */
    private function expensesBetween(Collection $expenses, Carbon $start, Carbon $end): Collection
    {
        return $expenses->filter(fn (Expense $expense): bool => $expense->date->betweenIncluded($start, $end->copy()->endOfDay()));
    }

    private function periodLabel(Carbon $start, Carbon $end): string
    {
        return $start->format('j M Y').' – '.$end->format('j M Y');
    }

    private function percentageChange(float $current, float $previous): ?float
    {
        return $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : null;
    }

    /**
     * @param  Collection<int, Expense>  $current
     * @param  Collection<int, Expense>  $previous
     * @return array{currentLabel: string, previousLabel: string, currentTotal: float, previousTotal: float, difference: float, percentageChange: float|null, currentHasExpenses: bool, previousHasExpenses: bool}
     */
    private function comparison(Collection $current, Collection $previous, string $currentLabel, string $previousLabel): array
    {
        $currentTotal = round((float) $current->sum('amount'), 2);
        $previousTotal = round((float) $previous->sum('amount'), 2);

        return [
            'currentLabel' => $currentLabel,
            'previousLabel' => $previousLabel,
            'currentTotal' => $currentTotal,
            'previousTotal' => $previousTotal,
            'difference' => round($currentTotal - $previousTotal, 2),
            'percentageChange' => $this->percentageChange($currentTotal, $previousTotal),
            'currentHasExpenses' => $current->isNotEmpty(),
            'previousHasExpenses' => $previous->isNotEmpty(),
        ];
    }

    /**
     * @param  Collection<int, Expense>  $expenses
     * @param  Collection<int, Income>  $incomes
     * @return array<int, array{monthKey: string, label: string, spent: float, income: float, net: float, isCurrent: bool, isFuture: bool, hasExpenses: bool, hasIncome: bool, expensesUrl: string}>
     */
    private function monthlyCashflow(Carbon $fyStart, Collection $expenses, Collection $incomes, Carbon $today): array
    {
        $spentByMonth = $expenses->groupBy(fn (Expense $expense): string => $expense->date->format('Y-m'));
        $incomeByMonth = $incomes->groupBy(fn (Income $income): string => $income->date->format('Y-m'));
        $currentKey = $today->format('Y-m');

        return collect(range(0, 11))->map(function (int $offset) use ($fyStart, $spentByMonth, $incomeByMonth, $currentKey, $today): array {
            $month = $fyStart->copy()->addMonths($offset);
            $key = $month->format('Y-m');
            $monthExpenses = $spentByMonth->get($key, collect());
            $monthIncomes = $incomeByMonth->get($key, collect());
            $spent = round((float) $monthExpenses->sum('amount'), 2);
            $income = round((float) $monthIncomes->sum('amount'), 2);

            return [
                'monthKey' => $key,
                'label' => $month->format('M'),
                'spent' => $spent,
                'income' => $income,
                'net' => round($income - $spent, 2),
                'isCurrent' => $key === $currentKey,
                'isFuture' => $key > $currentKey,
                'hasExpenses' => $monthExpenses->isNotEmpty(),
                'hasIncome' => $monthIncomes->isNotEmpty(),
                'expensesUrl' => route('expenses.index', $key === $currentKey
                    ? ['start_date' => $month->toDateString(), 'end_date' => $today->toDateString()]
                    : ['month' => $key]),
            ];
        })->all();
    }

    /**
     * @param  Collection<int, Expense>  $expenses
     * @return array{currentLabel: string, previousLabel: string, currentTotal: float, previousTotal: float, difference: float, percentageChange: float|null, currentHasExpenses: bool, previousHasExpenses: bool, expensesUrl: string, drivers: array<int, array{categoryId: int, name: string, currentTotal: float, previousTotal: float, difference: float, expensesUrl: string}>}
     */
    private function monthlyComparison(Collection $expenses, Carbon $month): array
    {
        $previousMonth = $month->copy()->subMonth();
        $current = $this->expensesBetween($expenses, $month, $month->copy()->endOfMonth());
        $previous = $this->expensesBetween($expenses, $previousMonth, $previousMonth->copy()->endOfMonth());
        $currentByCategory = $current->groupBy('category_id');
        $previousByCategory = $previous->groupBy('category_id');
        $drivers = $current->concat($previous)->unique('category_id')->map(function (Expense $expense) use ($currentByCategory, $previousByCategory, $month): array {
            $currentTotal = round((float) $currentByCategory->get($expense->category_id, collect())->sum('amount'), 2);
            $previousTotal = round((float) $previousByCategory->get($expense->category_id, collect())->sum('amount'), 2);

            return [
                'categoryId' => $expense->category_id,
                'name' => $expense->category->name,
                'currentTotal' => $currentTotal,
                'previousTotal' => $previousTotal,
                'difference' => round($currentTotal - $previousTotal, 2),
                'expensesUrl' => route('expenses.index', ['month' => $month->format('Y-m'), 'category_id' => $expense->category_id]),
            ];
        })->filter(fn (array $driver): bool => $driver['difference'] !== 0.0)
            ->sortByDesc(fn (array $driver): float => abs($driver['difference']))
            ->take(5)->values()->all();

        return [
            ...$this->comparison($current, $previous, $month->format('M Y'), $previousMonth->format('M Y')),
            'expensesUrl' => route('expenses.index', ['month' => $month->format('Y-m')]),
            'drivers' => $drivers,
        ];
    }

    /**
     * @param  Collection<int, Expense>  $expenses
     * @param  Collection<int, Expense>  $previousExpenses
     * @return array<int, array{id: int, name: string, total: float, share: float, previousTotal: float, change: float, changePercent: float|null, expensesUrl: string}>
     */
    private function categoryTotals(Collection $expenses, Collection $previousExpenses, Carbon $start, Carbon $end): array
    {
        $currentByCategory = $expenses->groupBy('category_id');
        $previousByCategory = $previousExpenses->groupBy('category_id');
        $spentTotal = (float) $expenses->sum('amount');

        return $expenses->concat($previousExpenses)->unique('category_id')
            ->map(function (Expense $expense) use ($currentByCategory, $previousByCategory, $spentTotal, $start, $end): array {
                $total = round((float) $currentByCategory->get($expense->category_id, collect())->sum('amount'), 2);
                $previousTotal = round((float) $previousByCategory->get($expense->category_id, collect())->sum('amount'), 2);

                return [
                    'id' => $expense->category_id,
                    'name' => $expense->category->name,
                    'total' => $total,
                    'share' => $spentTotal > 0 ? round($total / $spentTotal * 100, 1) : 0.0,
                    'previousTotal' => $previousTotal,
                    'change' => round($total - $previousTotal, 2),
                    'changePercent' => $this->percentageChange($total, $previousTotal),
                    'expensesUrl' => route('expenses.index', [
                        'category_id' => $expense->category_id,
                        'start_date' => $start->toDateString(),
                        'end_date' => $end->toDateString(),
                    ]),
                ];
            })->sortByDesc('total')->values()->all();
    }

    /**
     * @param  Collection<int, Expense>  $expenses
     * @return array<int, array{id: int, date: string, category: string, note: string|null, amount: float, url: string}>
     */
    private function biggestExpenses(Collection $expenses): array
    {
        return $expenses->sortByDesc('amount')->take(8)->values()
            ->map(fn (Expense $expense): array => [
                'id' => $expense->id,
                'date' => $expense->date->format('j M'),
                'category' => $expense->category->name,
                'note' => $expense->note,
                'amount' => (float) $expense->amount,
                'url' => route('expenses.edit', $expense),
            ])->all();
    }

    /**
     * @return array<int, array{year: int, label: string}>
     */
    private function availableFinancialYears(int $currentFyStart): array
    {
        $earliest = collect([
            Expense::query()->min('date'),
            Income::query()->min('date'),
        ])->filter()->min();
        $earliestDate = $earliest === null ? null : Date::parse($earliest);
        $earliestFyStart = $earliestDate === null
            ? $currentFyStart
            : min($currentFyStart, $earliestDate->month >= 4 ? $earliestDate->year : $earliestDate->year - 1);

        return array_map(
            fn (int $year): array => [
                'year' => $year,
                'label' => 'FY '.$year.'-'.substr((string) ($year + 1), 2),
            ],
            range($currentFyStart, $earliestFyStart),
        );
    }
}
