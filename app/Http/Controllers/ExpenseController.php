<?php

namespace App\Http\Controllers;

use App\Action\GetDefaultExpenseListAction;
use App\Action\GetTotalExpensesForCurrentMonthAction;
use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;

class ExpenseController extends Controller
{
    public function create()
    {
        return inertia('Expenses/Create', [
            'categories' => Category::all(),
        ]);
    }

    public function index()
    {
        return inertia('Expenses/Index', [
            'expenses' => app(GetDefaultExpenseListAction::class)->handle(),
            'total' => app(GetTotalExpensesForCurrentMonthAction::class)->handle(),
        ]);
    }

    public function store(ExpenseRequest $request)
    {
        Expense::create($request->validated());

        return redirect()->to('/expenses')->with('success', 'Expense created successfully.');
    }

    public function edit(Expense $expense)
    {
        return inertia('Expenses/Edit', [
            'expense' => $expense,
            'categories' => Category::all(),
        ]);
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        return redirect()->to('/expenses')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->to('/expenses')->with('success', 'Expense deleted successfully.');
    }
}
