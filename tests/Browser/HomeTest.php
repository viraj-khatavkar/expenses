<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * Helper: returns [fyStart, fyEnd] as Y-m-d strings for the current Indian financial year.
 */
function fyRange(): array
{
    $now = Date::today();
    $fyStartYear = $now->month >= 4 ? $now->year : $now->year - 1;

    return [
        "{$fyStartYear}-04-01",
        ($fyStartYear + 1).'-03-31',
    ];
}

it('shows correct total for current financial year', function ($amountOne, $amountTwo, $amountThree, $total) {
    $user = User::factory()->create();

    [$fyStart] = fyRange();

    // Inside current FY
    Expense::factory()->create(['date' => Date::now()->format('Y-m-d'), 'amount' => $amountOne]);
    Expense::factory()->create(['date' => $fyStart, 'amount' => $amountTwo]);

    // Outside current FY
    Expense::factory()->create(['date' => Date::parse($fyStart)->subDay()->format('Y-m-d'), 'amount' => $amountThree]);

    loginAs($user->email)
        ->assertSeeIn('#yearly-stat', $total);
})->with([
    [1000, 500, 123421, '1,500'],
    [10000, 500, 76426, '10,500'],
    [234573, 129834, 8452, '3,64,407'],
]);

it('shows correct total for april', function () {
    $user = User::factory()->create();
    $categoryIds = Category::factory()->count(10)->create()->pluck('id');

    $amounts = [18392, 27451, 35780, 12945, 49332, 6814, 31609, 19876, 15204, 25164];

    $fyStartYear = Date::today()->month >= 4 ? Date::today()->year : Date::today()->year - 1;

    foreach ($amounts as $amount) {
        $date = "{$fyStartYear}-04-".Str::padLeft(random_int(1, 30), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    loginAs($user->email)
        ->assertSeeIn('#April', '2,42,567');
});

it('shows correct total for april and may', function () {
    $user = User::factory()->create();
    $categoryIds = Category::factory()->count(10)->create()->pluck('id');

    $fyStartYear = Date::today()->month >= 4 ? Date::today()->year : Date::today()->year - 1;

    // April
    $amounts = [18392, 27451, 35780, 12945, 49332, 6814, 31609, 19876, 15204, 25164];

    foreach ($amounts as $amount) {
        $date = "{$fyStartYear}-04-".Str::padLeft(random_int(1, 30), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // May
    $amounts = [43123, 55217, 23819, 87433, 17654, 64351, 12789, 19096];

    foreach ($amounts as $amount) {
        $date = "{$fyStartYear}-05-".Str::padLeft(random_int(1, 31), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    loginAs($user->email)
        ->assertSeeIn('#April', '2,42,567')
        ->assertSeeIn('#May', '3,23,482');
});

it('shows correct total for all twelve months of financial year', function () {
    $user = User::factory()->create();
    $categoryIds = Category::factory()->count(10)->create()->pluck('id');

    $fyStartYear = Date::today()->month >= 4 ? Date::today()->year : Date::today()->year - 1;
    $fyEndYear = $fyStartYear + 1;

    $monthData = [
        // FY first half: April - December (same calendar year)
        ['year' => $fyStartYear, 'month' => '04', 'maxDay' => 30, 'amounts' => [18392, 27451, 35780, 12945, 49332, 6814, 31609, 19876, 15204, 25164], 'name' => 'April', 'expected' => '2,42,567'],
        ['year' => $fyStartYear, 'month' => '05', 'maxDay' => 31, 'amounts' => [43123, 55217, 23819, 87433, 17654, 64351, 12789, 19096], 'name' => 'May', 'expected' => '3,23,482'],
        ['year' => $fyStartYear, 'month' => '06', 'maxDay' => 30, 'amounts' => [31257, 48291, 17943, 26517, 38951, 21439, 17563, 12410], 'name' => 'June', 'expected' => '2,14,371'],
        ['year' => $fyStartYear, 'month' => '07', 'maxDay' => 31, 'amounts' => [55321, 42753, 36817, 21991, 71239, 49833, 15927, 4548], 'name' => 'July', 'expected' => '2,98,429'],
        ['year' => $fyStartYear, 'month' => '08', 'maxDay' => 31, 'amounts' => [38119, 27451, 19837, 45673, 31992, 12789, 22987, 3598], 'name' => 'August', 'expected' => '2,02,446'],
        ['year' => $fyStartYear, 'month' => '09', 'maxDay' => 30, 'amounts' => [73451, 55239, 41983, 61257, 38541, 27319, 52197, 216], 'name' => 'September', 'expected' => '3,50,203'],
        ['year' => $fyStartYear, 'month' => '10', 'maxDay' => 31, 'amounts' => [19237, 26451, 31829, 17593, 28917, 33157, 12759, 4992], 'name' => 'October', 'expected' => '1,74,935'],
        ['year' => $fyStartYear, 'month' => '11', 'maxDay' => 30, 'amounts' => [55064, 10671, 21006, 60082, 38945, 23391, 52316, 40832, 25945, 71718, 73681], 'name' => 'November', 'expected' => '4,73,651'],
        ['year' => $fyStartYear, 'month' => '12', 'maxDay' => 31, 'amounts' => [67411, 53971, 11131, 14741, 44722, 68726, 51021, 28804, 15867, 48906, 78458, 27956], 'name' => 'December', 'expected' => '5,11,714'],
        // FY second half: January - March (next calendar year)
        ['year' => $fyEndYear, 'month' => '01', 'maxDay' => 31, 'amounts' => [40391, 25816, 76586, 56009, 66933, 60431, 38183, 14164, 11664], 'name' => 'January', 'expected' => '3,90,177'],
        ['year' => $fyEndYear, 'month' => '02', 'maxDay' => 28, 'amounts' => [65029, 70461, 20856, 33419, 31714, 79136], 'name' => 'February', 'expected' => '3,00,615'],
        ['year' => $fyEndYear, 'month' => '03', 'maxDay' => 31, 'amounts' => [55064, 10671, 21006, 60082, 38945, 23391, 52316, 40832, 25945, 71718, 73681], 'name' => 'March', 'expected' => '4,73,651'],
    ];

    foreach ($monthData as $m) {
        foreach ($m['amounts'] as $amount) {
            $date = "{$m['year']}-{$m['month']}-".Str::padLeft(random_int(1, $m['maxDay']), 2, '0');
            Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
        }
    }

    // Outside FY — should not appear
    $outsideFyAmounts = [55064, 10671, 21006, 60082, 38945, 23391, 52316, 40832, 25945, 71718, 73681];

    foreach ($outsideFyAmounts as $amount) {
        $date = Date::parse("{$fyStartYear}-04-01")->subYear()->addMonths(random_int(0, 11))->format('Y-m-d');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    $browser = loginAs($user->email);

    foreach ($monthData as $m) {
        $browser->assertSeeIn("#{$m['name']}", $m['expected']);
    }
});

it('shows correct category totals within months', function () {
    $user = User::factory()->create();
    $grocery = Category::factory()->create(['name' => 'Grocery']);
    $starbucks = Category::factory()->create(['name' => 'Starbucks']);

    $fyStartYear = Date::today()->month >= 4 ? Date::today()->year : Date::today()->year - 1;

    // April - Grocery
    Expense::factory()->create(['date' => "{$fyStartYear}-04-10", 'amount' => 5000, 'category_id' => $grocery->id]);
    Expense::factory()->create(['date' => "{$fyStartYear}-04-15", 'amount' => 3000, 'category_id' => $grocery->id]);

    // April - Starbucks
    Expense::factory()->create(['date' => "{$fyStartYear}-04-20", 'amount' => 1500, 'category_id' => $starbucks->id]);

    // Outside FY - should not appear
    Expense::factory()->create(['date' => Date::parse("{$fyStartYear}-04-01")->subDay()->format('Y-m-d'), 'amount' => 99999, 'category_id' => $grocery->id]);

    loginAs($user->email)
        ->assertSeeIn('#April', '9,500')
        ->assertSeeIn('#April', 'Grocery')
        ->assertSeeIn('#April', '8,000')
        ->assertSeeIn('#April', 'Starbucks')
        ->assertSeeIn('#April', '1,500');
});
