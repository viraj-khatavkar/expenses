<?php

use App\Models\Income;
use App\Models\Source;
use App\Models\User;
use Illuminate\Support\Facades\Date;

it('lists income for the current financial year by default', function () {
    $user = User::factory()->create();
    $source = Source::factory()->create(['name' => 'Salary']);

    Income::factory()->create([
        'source_id' => $source->id,
        'amount' => 10000,
        'date' => Date::today()->startOfMonth(),
    ]);

    Income::factory()->create([
        'source_id' => $source->id,
        'amount' => 99999,
        'date' => Date::today()->subYears(2),
    ]);

    loginAs($user->email)
        ->navigate('/income')
        ->assertSee('Salary')
        ->assertSee('₹10,000.00')
        ->assertDontSee('₹99,999.00');
});

it('filters income by the selected financial year via the url', function () {
    $user = User::factory()->create();
    $source = Source::factory()->create(['name' => 'Freelance']);

    Income::factory()->create([
        'source_id' => $source->id,
        'amount' => 42000,
        'date' => '2024-06-15',
    ]);

    loginAs($user->email)
        ->navigate('/income?fy=2024')
        ->assertSee('FY 2024-25')
        ->assertSee('Freelance')
        ->assertSee('₹42,000.00');
});

it('can add a new income', function ($amount, $displayed) {
    $user = User::factory()->create();
    $source = Source::factory()->create(['name' => 'Bonus']);

    loginAs($user->email)
        ->navigate('/income/create')
        ->type('amount', (string) $amount)
        ->select('source_id', $source->id)
        ->press('Add Income')
        ->assertPathIs('/income')
        ->assertSee('Income created successfully.')
        ->assertSee($displayed)
        ->assertSee('Bonus');
})->with([
    [5000, '₹5,000.00'],
    [5000.50, '₹5,000.50'],
    [75000, '₹75,000.00'],
    [75000.89, '₹75,000.89'],
]);

it('can edit an income', function ($amount, $displayed) {
    $user = User::factory()->create();
    $source = Source::factory()->create(['name' => 'Salary']);
    $income = Income::factory()->create([
        'source_id' => $source->id,
        'amount' => 100,
        'date' => Date::today(),
    ]);

    loginAs($user->email)
        ->navigate('/income')
        ->click('Salary')
        ->assertPathIs('/income/'.$income->id.'/edit')
        ->type('amount', (string) $amount)
        ->press('Update')
        ->assertPathIs('/income')
        ->assertSee('Income updated successfully.')
        ->assertSee($displayed)
        ->assertDontSee('₹100.00');
})->with([
    [5000, '₹5,000.00'],
    [5000.50, '₹5,000.50'],
]);

it('can delete an income', function () {
    $user = User::factory()->create();
    $source = Source::factory()->create(['name' => 'Salary']);
    $income = Income::factory()->create([
        'source_id' => $source->id,
        'amount' => 100,
        'date' => Date::today(),
    ]);

    loginAs($user->email)
        ->navigate('/income/'.$income->id.'/edit')
        ->press('Delete')
        ->assertPathIs('/income')
        ->assertSee('Income deleted successfully.')
        ->assertDontSee('₹100.00');
});

it('shows validation errors for creating a new income', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/income/create')
        ->type('date', '')
        ->press('Add Income')
        ->assertPathIs('/income/create')
        ->assertSee('The date field is required')
        ->assertSee('The source field is required')
        ->assertSee('The amount field is required');
});
