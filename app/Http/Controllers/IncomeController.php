<?php

namespace App\Http\Controllers;

use App\Action\GetAvailableIncomeFinancialYearsAction;
use App\Action\GetIncomesForFinancialYearAction;
use App\Action\GetTotalIncomeForFinancialYearAction;
use App\Http\Requests\IncomeRequest;
use App\Models\Income;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Inertia\Response;

use function to_route;

final class IncomeController extends Controller
{
    public function index(Request $request): Response
    {
        $now = Date::today();
        $currentFyStart = $now->month >= 4 ? $now->year : $now->year - 1;

        $fy = (int) $request->input('fy', $currentFyStart);
        $fyStart = Carbon::create($fy, 4, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();

        $fyLabel = 'FY '.$fyStart->year.'-'.substr((string) $fyEnd->year, 2);

        return inertia('Income/Index', [
            'fy' => $fy,
            'fyLabel' => $fyLabel,
            'availableFys' => app(GetAvailableIncomeFinancialYearsAction::class)->handle($currentFyStart),
            'incomes' => app(GetIncomesForFinancialYearAction::class)->handle($fyStart, $fyEnd),
            'total' => app(GetTotalIncomeForFinancialYearAction::class)->handle($fyStart, $fyEnd),
        ]);
    }

    public function create(): Response
    {
        return inertia('Income/Create', [
            'sources' => Source::orderBy('name')->get(),
        ]);
    }

    public function store(IncomeRequest $request): RedirectResponse
    {
        Income::create($request->validated());

        return to_route('income.index')->with('success', 'Income created successfully.');
    }

    public function edit(Income $income): Response
    {
        return inertia('Income/Edit', [
            'income' => $income,
            'sources' => Source::orderBy('name')->get(),
        ]);
    }

    public function update(IncomeRequest $request, Income $income): RedirectResponse
    {
        $income->update($request->validated());

        return to_route('income.index')->with('success', 'Income updated successfully.');
    }

    public function destroy(Income $income): RedirectResponse
    {
        $income->delete();

        return to_route('income.index')->with('success', 'Income deleted successfully.');
    }
}
