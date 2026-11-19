<?php

use App\Models\InvestmentCategory;
use App\Models\InvestmentTransaction;
use App\Models\User;

it('records deposits and redemptions by category and calculates monthly net', function () {
    $user = User::factory()->create();

    loginAs($user->email)->resize(1280, 900)->click('Investments')
        ->press('Add transaction')->fill('amount', '125000.10')->fill('date', '2026-09-07')
        ->fill('category', 'Mutual funds')->fill('note', 'September purchase')->press('Save transaction')
        ->assertPathIs('/investments/2026-09')->assertSee('September purchase')
        ->assertSee('₹1,25,000.10')
        ->press('Add transaction')->click('Redemption')->fill('amount', '500.15')
        ->fill('category', 'Mutual funds')->fill('date', '2026-09-19')->press('Save transaction')
        ->assertSeeIn('[aria-labelledby="investment-category-heading"]', '₹1,24,499.95')
        ->assertSeeIn('[aria-labelledby="investment-category-heading"]', '₹1,25,000.10 in · ₹500.15 redeemed')
        ->assertSeeIn('[aria-label="Monthly investment summary"]', '₹1,24,499.95')
        ->press('Add transaction')->fill('amount', '100.16')->fill('category', 'Gold')->press('Save transaction')
        ->assertSeeIn('[aria-labelledby="investment-category-heading"]', 'Gold')
        ->assertSeeIn('[aria-labelledby="investment-category-heading"]', '₹1,24,499.95')
        ->assertSeeIn('[aria-label="Monthly investment summary"]', '₹1,24,600.11')
        ->click('[aria-label="Next month"]')->assertSee('No transactions in October 2026.')
        ->assertSeeIn('[aria-labelledby="investment-year-heading"] dl', '₹1,24,600.11')
        ->click('[aria-label="Monthly net investments"] a')->assertSee('September purchase')
        ->press('Set target')->fill('planned', '120000')->press('Save target')->assertSee('₹1,20,000')
        ->assertSee('₹4,600.11 above target')
        ->assertNoJavaScriptErrors();
});

it('edits a transaction and confirms deletion on a phone', function () {
    $user = User::factory()->create();
    $fund = InvestmentCategory::factory()->for($user)->create(['name' => 'Gold']);
    $entry = InvestmentTransaction::factory()->for($fund, 'category')->create(['date' => null, 'period' => '2026-09', 'amount_paise' => 80050]);
    $page = loginAs($user->email)->resize(375, 812)->press('Open sidebar')
        ->click('[role="dialog"] a[href="/investments"]')->navigate('/investments/2026-09')
        ->assertSee('Monthly total from sheet')->click('[data-test="transaction-'.$entry->id.'"]')
        ->fill('category', 'Index funds')->fill('amount', '1200.15')->press('Save transaction')
        ->assertSee('Index funds')->assertSee('₹1,200.15')->assertSee('Monthly total from sheet')
        ->click('[data-test="transaction-'.$entry->id.'"]')->press('Delete')->click('@confirm-delete')
        ->assertSee('Transaction deleted.')->assertSee('No transactions in September 2026.')
        ->assertNoJavaScriptErrors();

    expect($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();
});
