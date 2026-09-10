<?php

namespace App\Action;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

final readonly class GetExpenseDayGroupsAction
{
    public function __construct(private GetExpensesAction $getExpenses) {}

    /**
     * @return Collection<int, array{label: string, total: float, expenses: Collection<int, Expense>}>
     */
    public function handle(Carbon $startDate, Carbon $endDate, ?int $categoryId = null): Collection
    {
        return $this->getExpenses->handle($startDate, $endDate)
            ->when($categoryId !== null, fn (Builder $query): Builder => $query->where('category_id', $categoryId))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->with('category')
            ->get()
            ->groupBy(fn (Expense $expense): string => Date::parse($expense->date)->format('Y-m-d'))
            ->map(function (Collection $expenses, string $dateString) use ($startDate, $endDate): array {
                $date = Date::parse($dateString);

                $label = match (true) {
                    $date->isToday() => 'Today',
                    $date->isYesterday() => 'Yesterday',
                    $startDate->year !== $endDate->year => $date->format('D, d F Y'),
                    default => $date->format('D, d F'),
                };

                return [
                    'label' => $label,
                    'total' => (float) $expenses->sum('amount'),
                    'expenses' => $expenses->values(),
                ];
            })
            ->values();
    }
}
