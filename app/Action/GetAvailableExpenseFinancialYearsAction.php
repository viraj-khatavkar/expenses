<?php

namespace App\Action;

use App\Models\Expense;
use Illuminate\Support\Facades\Date;

final readonly class GetAvailableExpenseFinancialYearsAction
{
    /**
     * @return array<int, array{year: int, label: string}>
     */
    public function handle(int $currentFyStart): array
    {
        $earliestDate = Expense::query()->min('date');

        if ($earliestDate === null) {
            $earliestFyStart = $currentFyStart;
        } else {
            $earliest = Date::parse($earliestDate);
            $earliestFyStart = $earliest->month >= 4 ? $earliest->year : $earliest->year - 1;
        }

        $years = range($currentFyStart, $earliestFyStart);

        return array_map(
            fn (int $year): array => [
                'year' => $year,
                'label' => 'FY '.$year.'-'.substr((string) ($year + 1), 2),
            ],
            $years,
        );
    }
}
