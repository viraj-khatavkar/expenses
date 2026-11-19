<?php

namespace App\Http\Controllers;

use App\Action\GetInvestmentYearAction;
use App\Http\Requests\InvestmentMonthRequest;
use App\Http\Requests\InvestmentTransactionRequest;
use App\Models\InvestmentCategory;
use App\Models\InvestmentMonth;
use App\Models\InvestmentTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

final class InvestmentController extends Controller
{
    public function index(Request $request, GetInvestmentYearAction $overview, ?string $period = null): Response
    {
        $user = $request->user();
        $today = Date::today();
        $period ??= $today->format('Y-m');

        return inertia('Investments/Index', [
            ...$overview->handle($user, $period),
            'today' => $today->toDateString(),
            'categories' => InvestmentCategory::whereBelongsTo($user)->orderBy('name')->pluck('name'),
            'transactions' => InvestmentTransaction::forUser($user)->where('period', $period)
                ->with('category:id,name')->orderByDesc('date')->orderByDesc('id')
                ->get(['id', 'category_id', 'date', 'period', 'direction', 'amount_paise', 'note']),
        ]);
    }

    public function store(InvestmentTransactionRequest $request): RedirectResponse
    {
        return $this->saveTransaction($request, new InvestmentTransaction);
    }

    public function update(InvestmentTransactionRequest $request, int $transaction): RedirectResponse
    {
        return $this->saveTransaction($request, InvestmentTransaction::forUser($request->user())->findOrFail($transaction));
    }

    public function destroy(Request $request, int $transaction): RedirectResponse
    {
        $entry = InvestmentTransaction::forUser($request->user())->findOrFail($transaction);
        $entry->delete();

        return to_route('investments.index', ['period' => $entry->period])->with('success', 'Transaction deleted.');
    }

    public function target(InvestmentMonthRequest $request, string $period): RedirectResponse
    {
        [$year, $month] = explode('-', $period);
        $key = ['user_id' => $request->user()->getKey(), 'year' => (int) $year, 'month' => (int) $month];
        $target = $request->validated('planned');

        if ($target === null) {
            InvestmentMonth::where($key)->delete();
        } else {
            InvestmentMonth::upsert([...$key, 'planned' => $target], array_keys($key), ['planned']);
        }

        return to_route('investments.index', ['period' => $period])->with('success', 'Monthly target saved.');
    }

    private function saveTransaction(InvestmentTransactionRequest $request, InvestmentTransaction $transaction): RedirectResponse
    {
        $data = $request->validated();
        $period = substr($data['date'], 0, 7);
        if (strlen($data['date']) === 7 && $transaction->date !== null) {
            throw ValidationException::withMessages(['date' => 'Enter the full transaction date.']);
        }

        DB::transaction(function () use ($request, $transaction, $data, $period): void {
            $category = InvestmentCategory::forName($request->user(), $data['category']);
            $transaction->category()->associate($category);
            $transaction->fill([
                'period' => $period,
                'date' => strlen($data['date']) === 10 ? $data['date'] : null,
                'direction' => $data['direction'],
                'amount_paise' => (int) round((float) $data['amount'] * 100),
                'note' => $data['note'] ?? null,
            ])->save();
        });

        return to_route('investments.index', ['period' => $period])->with('success', 'Transaction saved.');
    }
}
