<?php

namespace App\Http\Controllers;

use App\Action\GetDefaultExpenseListAction;
use App\Action\GetTotalExpensesForCurrentMonthAction;
use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

use function to_route;

final class ExpenseController extends Controller
{
    public function create(): Response
    {
        return inertia('Expenses/Create', [
            'categories' => Category::all(),
        ]);
    }

    public function index(): Response
    {
        return inertia('Expenses/Index', [
            'expenses' => app(GetDefaultExpenseListAction::class)->handle(),
            'total' => app(GetTotalExpensesForCurrentMonthAction::class)->handle(),
        ]);
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        Expense::create($request->validated());

        return to_route('expenses.index')->with('success', 'Expense created successfully.');
    }

    public function edit(Expense $expense): Response
    {
        return inertia('Expenses/Edit', [
            'expense' => $expense,
            'categories' => Category::all(),
        ]);
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return to_route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return to_route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
