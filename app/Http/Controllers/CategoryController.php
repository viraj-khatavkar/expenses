<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Inertia\Response;

use function to_route;

final class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

        return inertia('Categories/Index', [
            'fyLabel' => 'FY '.$fyStart->year.'-'.substr((string) $fyEnd->year, 2),
            'categories' => Category::orderBy('name')
                ->withCount(['expenses' => $withinFinancialYear])
                ->withSum(['expenses as expenses_total' => $withinFinancialYear], 'amount')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return inertia('Categories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'max:100'],
        ]);

        Category::create($attributes);

        return to_route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): Response
    {
        return inertia('Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'max:100'],
        ]);

        $category->name = $request->name;
        $category->save();

        return to_route('categories.index')->with('success', 'Category updated successfully.');
    }
}
