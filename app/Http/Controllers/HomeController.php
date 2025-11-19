<?php

namespace App\Http\Controllers;

use App\Action\GetExpensesGroupedByCategoryForYear;
use App\Action\GetExpensesGroupedByMonthForYear;
use App\Action\GetTotalExpensesForCurrentYearAction;
use Illuminate\Support\Facades\Date;

use function inertia;

class HomeController extends Controller
{
    public function __invoke()
    {
        $thisYearTotal = app(GetTotalExpensesForCurrentYearAction::class)->handle();
        $thisYearMonthlyTotals = app(GetExpensesGroupedByMonthForYear::class)->handle(Date::now());
        $thisYearCategoryTotals = app(GetExpensesGroupedByCategoryForYear::class)->handle(Date::now());

        return inertia('Home', [
            'thisYearTotal' => $thisYearTotal,
            'thisYearMonthlyTotals' => $thisYearMonthlyTotals,
            'thisYearCategoryTotals' => $thisYearCategoryTotals,
        ]);
    }
}
