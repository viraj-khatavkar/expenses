<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Str;

it('shows correct total for current year', function ($amountOne, $amountTwo, $amountThree, $total) {
    $user = User::factory()->create();

    Expense::factory()->create(['date' => Date::now()->format('Y-m-d'), 'amount' => $amountOne]);
    Expense::factory()->create(['date' => Date::now()->startOfYear()->format('Y-m-d'), 'amount' => $amountTwo]);
    Expense::factory()->create(['date' => Date::now()->subYear()->format('Y-m-d'), 'amount' => $amountThree]);

    loginAs($user->email)
        ->assertSee('This Year')
        ->assertSeeIn('#yearly-stat', $total);
})->with([
    [1000, 500, 123421, '1,500'],
    [10000, 500, 76426, '10,500'],
    [234573, 129834, 8452, '3,64,407'],
]);

it('shows correct total for january', function () {
    $user = User::factory()->create();
    $categoryIds = Category::factory()->count(10)->create()->pluck('id');

    $amounts = [18392, 27451, 35780, 12945, 49332, 6814, 31609, 19876, 15204, 25164];

    foreach ($amounts as $amount) {
        $date = date('Y').'-01-'.Str::padLeft(random_int(1, 31), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    loginAs($user->email)
        ->assertSeeIn('#January', '2,42,567');
});

it('shows correct total for jan and feb', function () {
    $user = User::factory()->create();
    $categoryIds = Category::factory()->count(10)->create()->pluck('id');

    $amounts = [18392, 27451, 35780, 12945, 49332, 6814, 31609, 19876, 15204, 25164];

    foreach ($amounts as $amount) {
        $date = date('Y').'-01-'.Str::padLeft(random_int(1, 31), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    $amounts = [43123, 55217, 23819, 87433, 17654, 64351, 12789, 19096];

    foreach ($amounts as $amount) {
        $date = date('Y').'-02-'.Str::padLeft(random_int(1, 28), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    loginAs($user->email)
        ->assertSeeIn('#January', '2,42,567')
        ->assertSeeIn('#February', '3,23,482');
});

it('shows correct total for all twelve months', function () {
    $user = User::factory()->create();
    $categoryIds = Category::factory()->count(10)->create()->pluck('id');

    // january
    $amounts = [18392, 27451, 35780, 12945, 49332, 6814, 31609, 19876, 15204, 25164];

    foreach ($amounts as $amount) {
        $date = date('Y').'-01-'.Str::padLeft(random_int(1, 31), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // february
    $amounts = [43123, 55217, 23819, 87433, 17654, 64351, 12789, 19096];

    foreach ($amounts as $amount) {
        $date = date('Y').'-02-'.Str::padLeft(random_int(1, 28), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // march
    $amounts = [31257, 48291, 17943, 26517, 38951, 21439, 17563, 12410];

    foreach ($amounts as $amount) {
        $date = date('Y').'-03-'.Str::padLeft(random_int(1, 31), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // april
    $amounts = [55321, 42753, 36817, 21991, 71239, 49833, 15927, 4548];

    foreach ($amounts as $amount) {
        $date = date('Y').'-04-'.Str::padLeft(random_int(1, 30), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // may
    $amounts = [38119, 27451, 19837, 45673, 31992, 12789, 22987, 3598];

    foreach ($amounts as $amount) {
        $date = date('Y').'-05-'.Str::padLeft(random_int(1, 31), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // june
    $amounts = [73451, 55239, 41983, 61257, 38541, 27319, 52197, 216];

    foreach ($amounts as $amount) {
        $date = date('Y').'-06-'.Str::padLeft(random_int(1, 30), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // july
    $amounts = [19237, 26451, 31829, 17593, 28917, 33157, 12759, 4992];

    foreach ($amounts as $amount) {
        $date = date('Y').'-07-'.Str::padLeft(random_int(1, 31), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // august
    $amounts = [55064, 10671, 21006, 60082, 38945, 23391, 52316, 40832, 25945, 71718, 73681];

    foreach ($amounts as $amount) {
        $date = date('Y').'-08-'.Str::padLeft(random_int(1, 31), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // september
    $amounts = [67411, 53971, 11131, 14741, 44722, 68726, 51021, 28804, 15867, 48906, 78458, 27956];

    foreach ($amounts as $amount) {
        $date = date('Y').'-09-'.Str::padLeft(random_int(1, 30), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // october
    $amounts = [40391, 25816, 76586, 56009, 66933, 60431, 38183, 14164, 11664];

    foreach ($amounts as $amount) {
        $date = date('Y').'-10-'.Str::padLeft(random_int(1, 31), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // november
    $amounts = [65029, 70461, 20856, 33419, 31714, 79136];

    foreach ($amounts as $amount) {
        $date = date('Y').'-11-'.Str::padLeft(random_int(1, 30), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // december
    $amounts = [55064, 10671, 21006, 60082, 38945, 23391, 52316, 40832, 25945, 71718, 73681];

    foreach ($amounts as $amount) {
        $date = date('Y').'-12-'.Str::padLeft(random_int(1, 31), 2, '0');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    // random month more than a year old
    $amounts = [55064, 10671, 21006, 60082, 38945, 23391, 52316, 40832, 25945, 71718, 73681];

    foreach ($amounts as $amount) {
        $date = Date::now()->subYears(2)->addMonths(random_int(1, 11))->format('Y-m-d');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryIds->random()]);
    }

    loginAs($user->email)
        ->assertSeeIn('#January', '2,42,567')
        ->assertSeeIn('#February', '3,23,482')
        ->assertSeeIn('#March', '2,14,371')
        ->assertSeeIn('#May', '2,02,446')
        ->assertSeeIn('#June', '3,50,203')
        ->assertSeeIn('#July', '1,74,935')
        ->assertSeeIn('#August', '4,73,651')
        ->assertSeeIn('#September', '5,11,714')
        ->assertSeeIn('#October', '3,90,177')
        ->assertSeeIn('#November', '3,00,615')
        ->assertSeeIn('#December', '4,73,651');
});

it('shows correct total for all categories', function () {
    $user = User::factory()->create();
    $categoryOne = Category::factory()->create(['name' => 'Grocery']);
    $categoryTwo = Category::factory()->create(['name' => 'Starbucks']);
    $categoryThree = Category::factory()->create(['name' => 'Food Delivery']);

    // category 1
    $amounts = [18392, 27451, 35780, 12945, 49332, 6814, 31609, 19876, 15204, 25164];

    foreach ($amounts as $amount) {
        $date = Date::now()->startOfYear()->addMonths(random_int(1, 11))->format('Y-m-d');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryOne->id]);
    }

    // category 2
    $amounts = [43123, 55217, 23819, 87433, 17654, 64351, 12789, 19096];

    foreach ($amounts as $amount) {
        $date = Date::now()->startOfYear()->addMonths(random_int(1, 11))->format('Y-m-d');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryTwo->id]);
    }

    // category 3
    $amounts = [31257, 48291, 17943, 26517, 38951, 21439, 17563, 12410];

    foreach ($amounts as $amount) {
        $date = Date::now()->startOfYear()->addMonths(random_int(1, 11))->format('Y-m-d');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryThree->id]);
    }

    // category 3 - older than this year so not calculated
    $amounts = [31257, 48291, 17943, 26517, 38951, 21439, 17563, 12410];

    foreach ($amounts as $amount) {
        $date = Date::now()->subYears(2)->addMonths(random_int(1, 11))->format('Y-m-d');
        Expense::factory()->create(['date' => $date, 'amount' => $amount, 'category_id' => $categoryThree->id]);
    }

    loginAs($user->email)
        ->assertSeeIn('#Grocery', '2,42,567')
        ->assertSeeIn('#Starbucks', '3,23,482')
        ->assertSeeIn('#Food-Delivery', '2,14,371');
});
