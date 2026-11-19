<?php

use App\Models\InvestmentCategory;
use App\Models\InvestmentMonth;
use App\Models\InvestmentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Date;

it('seeds sample targets and transactions for the demo account', function () {
    $this->seed();
    $user = User::sole();
    expect(InvestmentMonth::whereBelongsTo($user)->orderBy('month')->pluck('month')->all())->toBe([1, 2, 3])
        ->and(InvestmentTransaction::forUser($user)->sole()->category->name)->toBe('Mutual funds');
});

it('requires sign in to read or write investments', function () {
    $this->get('/investments')->assertRedirectToRoute('login');
    $this->post('/investments/transactions')->assertRedirectToRoute('login');
    $this->put('/investments/2026-09/target')->assertRedirectToRoute('login');
});

it('totals signed activity by calendar month and year for only its owner', function () {
    $this->travelTo(Date::parse('2026-02-20'));
    $user = User::factory()->create();
    $this->actingAs($user)->withoutVite();
    $fund = InvestmentCategory::factory()->for($user)->create(['name' => 'Index funds']);
    $cash = InvestmentCategory::factory()->for($user)->create(['name' => 'Emergency fund']);
    InvestmentTransaction::factory()->for($fund, 'category')->create(['date' => '2026-02-05', 'amount_paise' => 210010]);
    InvestmentTransaction::factory()->for($cash, 'category')->create(['date' => '2026-02-10', 'amount_paise' => 50005]);
    InvestmentTransaction::factory()->for($cash, 'category')->create(['date' => '2026-02-12', 'direction' => 'redemption', 'amount_paise' => 100020]);
    InvestmentTransaction::factory()->for($fund, 'category')->create(['date' => null, 'period' => '2026-01', 'amount_paise' => 24000]);
    InvestmentTransaction::factory()->for($cash, 'category')->create(['date' => '2025-12-31', 'amount_paise' => 800]);
    InvestmentTransaction::factory()->create(['date' => '2026-02-10']);
    InvestmentMonth::factory()->for($user)->create(['year' => 2026, 'month' => 2, 'planned' => 2000.05]);
    InvestmentMonth::factory()->for($user)->create(['year' => 2026, 'month' => 3, 'planned' => 1000]);
    InvestmentMonth::factory()->create(['year' => 2026, 'month' => 2, 'planned' => 7000]);

    $response = $this->get('/investments')->assertSuccessful();
    $props = $response->inertiaProps();
    expect($props['selected'])->toMatchArray([
        'period' => '2026-02', 'depositedPaise' => 260015, 'redeemedPaise' => 100020, 'netPaise' => 159995, 'targetPaise' => 200005,
    ])->and($props['yearNetPaise'])->toBe(183995)
        ->and($props['yearTargetPaise'])->toBe(300005)
        ->and(array_column($props['months'], 'period'))->toBe(['2026-03', '2026-02', '2026-01'])
        ->and(array_column($props['transactions'], 'date'))->toBe(['2026-02-12', '2026-02-10', '2026-02-05'])
        ->and($props['categories'])->toBe(['Emergency fund', 'Index funds']);
    expect($this->get('/investments/2026-01')->inertiaProps('transactions.0.date'))->toBeNull();
    expect($this->get('/investments/2025-12')->inertiaProps('yearNetPaise'))->toBe(800);
});

it('records separate entries in a reusable category and recalculates after edits and deletion', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->withoutVite();
    $data = ['direction' => 'deposit', 'category' => 'Index funds', 'amount' => '300.05', 'date' => '2026-05-12', 'note' => 'Monthly purchase'];
    $this->post('/investments/transactions', $data)->assertRedirectToRoute('investments.index', ['period' => '2026-05']);
    $this->post('/investments/transactions', [...$data, 'category' => ' INDEX   FUNDS ', 'amount' => '5.20'])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('investment_categories', 1);
    expect($this->get('/investments/2026-05')->inertiaProps('selected.netPaise'))->toBe(30525);
    $entry = InvestmentTransaction::firstOrFail();

    $this->putJson('/investments/transactions/'.$entry->id, [...$data, 'date' => '2026-05'])
        ->assertUnprocessable()->assertJsonValidationErrors('date');

    $this->put('/investments/transactions/'.$entry->id, [...$data, 'direction' => 'redemption', 'category' => 'Gold', 'date' => '2026-06-01'])
        ->assertRedirectToRoute('investments.index', ['period' => '2026-06']);
    expect($this->get('/investments/2026-05')->inertiaProps('selected.netPaise'))->toBe(520);
    expect($this->get('/investments/2026-06')->inertiaProps('selected.netPaise'))->toBe(-30005);
    expect($entry->fresh()->category->name)->toBe('Gold');

    $this->delete('/investments/transactions/'.$entry->id)->assertRedirectToRoute('investments.index', ['period' => '2026-06']);
    $this->assertSoftDeleted($entry);
    expect($this->get('/investments/2026-06')->inertiaProps('yearNetPaise'))->toBe(520);
});

it('lets the owner recategorize a monthly opening entry without inventing its day', function () {
    $user = User::factory()->create();
    $category = InvestmentCategory::factory()->for($user)->create();
    $entry = InvestmentTransaction::factory()->for($category, 'category')->create(['period' => '2026-09', 'date' => null]);
    $this->actingAs($user)->put('/investments/transactions/'.$entry->id, [
        'category' => 'Gold', 'direction' => 'deposit', 'amount' => '1200', 'date' => '2026-09',
    ])->assertSessionHasNoErrors();
    expect($entry->fresh()->date)->toBeNull()->and($entry->fresh()->category->name)->toBe('Gold');
});

it('keeps the monthly target independent of cash movements', function () {
    $this->actingAs(User::factory()->create())->withoutVite();
    $this->put('/investments/2026-09/target', ['planned' => '100.05'])->assertSessionHasNoErrors();
    $this->put('/investments/2026-09/target', ['planned' => '0'])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('investment_months', 1);
    expect($this->get('/investments/2026-09')->inertiaProps('selected.targetPaise'))->toBe(0);
    $this->put('/investments/2026-09/target', ['planned' => null])->assertSessionHasNoErrors();
    $this->assertDatabaseEmpty('investment_months');
    $this->assertDatabaseEmpty('investment_transactions');
});

it('protects other accounts and rejects invalid periods', function () {
    $this->actingAs(User::factory()->create());
    $entry = InvestmentTransaction::factory()->create();
    $this->put('/investments/transactions/'.$entry->id, [
        'direction' => 'deposit', 'amount' => 50, 'date' => '2026-05-01', 'category' => 'Private',
    ])->assertNotFound();
    $this->delete('/investments/transactions/'.$entry->id)->assertNotFound();
    $this->get('/investments/2026-13')->assertNotFound();
    $this->put('/investments/2026-00/target')->assertNotFound();
    $this->putJson('/investments/2026-01/target', ['planned' => -1])->assertJsonValidationErrors('planned');
    $this->assertDatabaseCount('investment_categories', 1);
    $this->assertNotSoftDeleted($entry);
});

it('rejects invalid transaction inputs before creating categories or entries', function (string $field, mixed $value) {
    $this->actingAs(User::factory()->create());
    $data = ['direction' => 'deposit', 'category' => 'Stocks', 'amount' => '10.25', 'date' => '2026-01-01'];
    $this->postJson('/investments/transactions', [...$data, $field => $value])->assertUnprocessable()->assertJsonValidationErrors($field);
    $this->assertDatabaseEmpty('investment_categories');
    $this->assertDatabaseEmpty('investment_transactions');
})->with([
    ['amount', 0], ['amount', -10], ['amount', '1.009'], ['amount', '10000000000'],
    ['date', '2026-02-30'], ['date', '2026-02'], ['direction', 'other'], ['category', '  '],
]);
