<?php

namespace App\Http\Controllers;

use App\Action\GetExpensesGroupedByMonthForYear;
use App\Action\GetTotalExpensesForCurrentYearAction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

use function inertia;

class HomeController extends Controller
{
    public function __invoke()
    {
        $now = Date::today();
        $fyStart = $now->month >= 4
            ? Carbon::create($now->year, 4, 1)
            : Carbon::create($now->year - 1, 4, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();

        $fyLabel = 'FY '.$fyStart->year.'-'.substr((string) $fyEnd->year, 2);

        return inertia('Home', [
            'fyLabel' => $fyLabel,
            'thisYearTotal' => app(GetTotalExpensesForCurrentYearAction::class)->handle($fyStart, $fyEnd),
            'thisYearMonthlyTotals' => app(GetExpensesGroupedByMonthForYear::class)->handle($fyStart, $fyEnd),
        ]);
    }
}
