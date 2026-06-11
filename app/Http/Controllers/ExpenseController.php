<?php

namespace App\Http\Controllers;

use App\Action\GetExpensesForMonthAction;
use App\Action\GetTotalExpensesForMonthAction;
use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function index(Request $request): Response
    {
        $currentMonth = Date::today()->startOfMonth();
        $selectedMonth = $this->resolveMonth($request->input('month'), $currentMonth);

        return inertia('Expenses/Index', [
            'month' => $selectedMonth->format('Y-m'),
            'monthLabel' => $selectedMonth->format('F Y'),
            'isCurrentMonth' => $selectedMonth->equalTo($currentMonth),
            'expenseGroups' => app(GetExpensesForMonthAction::class)->handle($selectedMonth),
            'total' => app(GetTotalExpensesForMonthAction::class)->handle($selectedMonth),
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

    private function resolveMonth(?string $month, Carbon $currentMonth): Carbon
    {
        if ($month === null) {
            return $currentMonth->copy();
        }

        try {
            return Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        } catch (InvalidFormatException) {
            return $currentMonth->copy();
        }
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
