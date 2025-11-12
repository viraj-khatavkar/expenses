<?php

namespace App\Http\Controllers;

use App\Action\GetDefaultExpenseListAction;
use App\Action\GetTotalExpensesForCurrentMonthAction;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;

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
        $expenses = app(GetDefaultExpenseListAction::class)->handle();
        $total = app(GetTotalExpensesForCurrentMonthAction::class)->handle();

        return inertia('Expenses/Index', [
            'expenses' => $expenses,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
        ]);

        Expense::create($attributes);

        return redirect()->to('/expenses')->with('success', 'Expense created successfully.');
    }

    public function edit(Expense $expense)
    {
        return inertia('Expenses/Edit', [
            'expense' => $expense,
            'categories' => Category::all(),
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
        ]);

        $expense->update($data);

        return redirect()->to('/expenses')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return response()->json();
    }
}
