<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Inertia\Response;

use function to_route;

final class SourceController extends Controller
{
    public function index(): Response
    {
        $now = Date::today();
        $fyStart = $now->month >= 4
            ? Carbon::create($now->year, 4, 1)
            : Carbon::create($now->year - 1, 4, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();

        $withinFinancialYear = fn (Builder $query) => $query
            ->where('date', '>=', $fyStart->format('Y-m-d'))
            ->where('date', '<=', $fyEnd->format('Y-m-d'));

        return inertia('Sources/Index', [
            'fyLabel' => 'FY '.$fyStart->year.'-'.substr((string) $fyEnd->year, 2),
            'sources' => Source::orderBy('name')
                ->withCount(['incomes' => $withinFinancialYear])
                ->withSum(['incomes as incomes_total' => $withinFinancialYear], 'amount')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return inertia('Sources/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'max:100'],
        ]);

        Source::create($attributes);

        return to_route('sources.index')->with('success', 'Source created successfully.');
    }

    public function edit(Source $source): Response
    {
        return inertia('Sources/Edit', [
            'source' => $source,
        ]);
    }

    public function update(Request $request, Source $source): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'max:100'],
        ]);

        $source->name = $request->name;
        $source->save();

        return to_route('sources.index')->with('success', 'Source updated successfully.');
    }
}
