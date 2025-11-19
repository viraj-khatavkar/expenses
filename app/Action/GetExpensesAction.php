<?php

namespace App\Action;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final readonly class GetExpensesAction
{
    public function handle(Carbon $startDate, Carbon $endDate): Builder
    {
        return Expense::query()
            ->where('date', '>=', $startDate->format('Y-m-d'))
            ->where('date', '<=', $endDate->format('Y-m-d'));
    }
}
