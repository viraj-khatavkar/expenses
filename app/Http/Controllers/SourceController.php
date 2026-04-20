<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

use function to_route;

final class SourceController extends Controller
{
    public function index(): Response
    {
        return inertia('Sources/Index', [
            'sources' => Source::orderBy('name')->get(),
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
