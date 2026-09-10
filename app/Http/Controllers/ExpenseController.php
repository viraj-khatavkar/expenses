<?php

namespace App\Http\Controllers;

use App\Action\GetExpenseDayGroupsAction;
use App\Http\Requests\ExpenseIndexRequest;
use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Inertia\Response;

use function to_route;

final class ExpenseController extends Controller
{
    public function create(): Response
    {
        return inertia('Expenses/Create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function index(ExpenseIndexRequest $request, GetExpenseDayGroupsAction $getExpenseDayGroups): Response
    {
        $filters = $request->validated();
        $currentMonth = Date::today()->startOfMonth();
        $isDateRange = isset($filters['start_date'], $filters['end_date']);
        $startDate = $isDateRange
            ? Date::parse($filters['start_date'])
            : Date::createFromFormat('!Y-m', $filters['month'] ?? $currentMonth->format('Y-m'));
        $endDate = $isDateRange ? Date::parse($filters['end_date']) : $startDate->copy()->endOfMonth();
        $category = isset($filters['category_id']) ? Category::query()->findOrFail($filters['category_id'], ['id', 'name']) : null;
        $expenseGroups = $getExpenseDayGroups->handle($startDate, $endDate, $category?->id);

        return inertia('Expenses/Index', [
            'month' => $startDate->format('Y-m'),
            'monthLabel' => $startDate->format('F Y'),
            'isCurrentMonth' => ! $isDateRange && $startDate->equalTo($currentMonth),
            'isDateRange' => $isDateRange,
            'periodLabel' => $isDateRange ? $startDate->format('d M Y').' to '.$endDate->format('d M Y') : $startDate->format('F Y'),
            'categoryFilter' => $category,
            'expensesUrl' => route('expenses.index'),
            'expenseGroups' => $expenseGroups,
            'total' => round((float) $expenseGroups->sum('total'), 2),
        ]);
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $expense = Expense::create($request->validated());

        return to_route('expenses.index', $this->monthRouteParameters($expense))
            ->with('success', 'Expense created successfully.');
    }

    public function edit(Expense $expense): Response
    {
        return inertia('Expenses/Edit', [
            'expense' => $expense,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return to_route('expenses.index', $this->monthRouteParameters($expense))
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $parameters = $this->monthRouteParameters($expense);

        $expense->delete();

        return to_route('expenses.index', $parameters)
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Send the user back to the month the expense belongs to, so the record
     * they just touched is visible on the index.
     *
     * @return array{month?: string}
     */
    private function monthRouteParameters(Expense $expense): array
    {
        $month = Date::parse($expense->date)->format('Y-m');

        return $month === Date::today()->format('Y-m') ? [] : ['month' => $month];
    }
}
