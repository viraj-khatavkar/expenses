<?php

namespace App\Action;

use App\Models\Income;
use Illuminate\Support\Facades\Date;

final readonly class GetAvailableIncomeFinancialYearsAction
{
    /**
     * Return the list of financial years (by their starting calendar year)
     * that should appear in the income filter dropdown.
     *
     * Always includes the current financial year and extends back to the
     * earliest recorded income.
     *
     * @return array<int, array{year: int, label: string}>
     */
    public function handle(int $currentFyStart): array
    {
        $earliestDate = Income::query()->min('date');

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
