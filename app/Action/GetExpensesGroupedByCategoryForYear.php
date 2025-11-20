<?php

namespace App\Action;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

use function app;

final readonly class GetExpensesGroupedByCategoryForYear
{
    public function handle(Carbon $date)
    {
        return app(GetExpensesAction::class)
            ->handle($date->copy()->startOfYear(), $date->copy()->endOfYear())
            ->with('category')
            ->orderByDesc('date')
            ->get()
            ->groupBy(fn (Expense $expense) => $expense->category->name)
            ->map(fn (Collection $expenses, string $category) => [
                'category' => $category,
                'total' => $expenses->sum('amount'),
            ])
            ->values();
    }
}
