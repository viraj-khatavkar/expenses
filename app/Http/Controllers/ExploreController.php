<?php

namespace App\Http\Controllers;

use App\Action\GetAvailableExpenseFinancialYearsAction;
use App\Action\GetExpenseTotalsByCategoryForFinancialYearAction;
use App\Action\GetTotalExpensesForCurrentYearAction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Inertia\Response;

use function inertia;

final class ExploreController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $now = Date::today();
        $currentFyStart = $now->month >= 4 ? $now->year : $now->year - 1;

        $fy = (int) $request->input('fy', $currentFyStart);
        $fyStart = Carbon::create($fy, 4, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();

        $fyLabel = 'FY '.$fyStart->year.'-'.substr((string) $fyEnd->year, 2);

        return inertia('Explore/Index', [
            'fy' => $fy,
            'fyLabel' => $fyLabel,
            'availableFys' => app(GetAvailableExpenseFinancialYearsAction::class)->handle($currentFyStart),
            'categoryTotals' => app(GetExpenseTotalsByCategoryForFinancialYearAction::class)->handle($fyStart, $fyEnd),
            'grandTotal' => app(GetTotalExpensesForCurrentYearAction::class)->handle($fyStart, $fyEnd),
        ]);
    }
}
