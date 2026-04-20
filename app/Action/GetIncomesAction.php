<?php

namespace App\Action;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final readonly class GetIncomesAction
{
    public function handle(Carbon $startDate, Carbon $endDate): Builder
    {
        return Income::query()
            ->where('date', '>=', $startDate->format('Y-m-d'))
            ->where('date', '<=', $endDate->format('Y-m-d'));
    }
}
