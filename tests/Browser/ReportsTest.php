<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    $this->travelTo(Date::parse('2026-09-10'));
});

afterEach(function () {
    $this->travelBack();
});

it('shows short cashflow amounts for the financial year', function () {
    $user = User::factory()->create();
    Expense::factory()->create(['amount' => 51000, 'date' => Date::today()]);
    Income::factory()->create(['amount' => 265000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSeeIn('[data-testid="cashflow-summary"]', '₹2.65L')
        ->assertSeeIn('[data-testid="cashflow-summary"]', '₹51K')
        ->assertSeeIn('[data-testid="cashflow-summary"]', 'Net cashflow')
        ->assertSeeIn('[data-testid="cashflow-summary"]', '+₹2.14L')
        ->assertSee('Income minus recorded expenses')
        ->assertSee('80.8% of recorded income')
        ->assertNoJavaScriptErrors();
});

it('shows negative net cashflow when expenses exceed income', function () {
    $user = User::factory()->create();
    Expense::factory()->create(['amount' => 50000, 'date' => Date::today()]);
    Income::factory()->create(['amount' => 30000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSeeIn('[data-testid="cashflow-summary"]', 'Net cashflow')
        ->assertSeeIn('[data-testid="cashflow-summary"]', '−₹20K');
});

it('includes all expenses in the summary', function () {
    $user = User::factory()->create();
    Income::factory()->create(['amount' => 200000, 'date' => Date::today()]);
    Expense::factory()->create(['amount' => 50000, 'date' => Date::today()]);
    Expense::factory()->create(['amount' => 90000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSeeIn('[data-testid="cashflow-summary"]', '₹1.4L')
        ->assertSeeIn('[data-testid="cashflow-summary"]', '+₹60K')
        ->assertSee('30% of recorded income');
});

it('shows the actual net balance after a large expense', function () {
    $user = User::factory()->create();
    Income::factory()->create(['amount' => 100000, 'date' => Date::today()]);
    Expense::factory()->create(['amount' => 55000, 'date' => Date::today()]);
    Expense::factory()->create(['amount' => 75000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSeeIn('[data-testid="cashflow-summary"]', '−₹30K')
        ->assertSee('-30% of recorded income');
});

it('includes large expenses in category totals', function () {
    $user = User::factory()->create();
    $grocery = Category::factory()->create(['name' => 'Grocery']);
    $hospital = Category::factory()->create(['name' => 'Hospital']);
    Expense::factory()->count(3)->create(['category_id' => $grocery->id, 'amount' => 1000, 'date' => Date::today()]);
    Expense::factory()->create(['category_id' => $hospital->id, 'amount' => 100000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSeeIn('[data-testid="report-category-'.$grocery->id.'"]', '₹3K')
        ->assertSeeIn('[data-testid="report-category-'.$hospital->id.'"]', '₹1L')
        ->assertSee('All expenses');
});

it('states when no income is recorded', function () {
    $user = User::factory()->create();
    Expense::factory()->create(['amount' => 50000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSeeIn('[data-testid="cashflow-summary"]', '−₹50K')
        ->assertSee('No income recorded in this period');
});

it('shows short monthly cashflow amounts beside both bars', function (int $viewportWidth) {
    $user = User::factory()->create();
    Expense::factory()->create(['amount' => 5432.10, 'date' => '2025-04-10']);
    Income::factory()->create(['amount' => 123456.78, 'date' => '2025-04-05']);

    $april = '[data-testid="cashflow-2025-04"]';
    $may = '[data-testid="cashflow-2025-05"]';

    $page = loginAs($user->email)
        ->resize($viewportWidth, 900)
        ->navigate('/reports?fy=2025')
        ->assertSee('Monthly cashflow')
        ->assertSeeIn($april.' [data-testid="income-amount"]', '₹1.23L')
        ->assertSeeIn($april.' [data-testid="spent-amount"]', '₹5.43K')
        ->assertSeeIn($april, 'Net +₹1.18L')
        ->assertSeeIn($may.' [data-testid="income-amount"]', '₹0')
        ->assertSeeIn($may.' [data-testid="spent-amount"]', '₹0')
        ->assertSeeIn($may, 'Net —')
        ->assertSeeIn($may, 'No records')
        ->assertNoJavaScriptErrors();

    expect($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();

    $barWidths = $page->script(<<<'JS'
        Array.from(
            document.querySelectorAll('[data-testid="cashflow-bar-track"]'),
            (track) => track.getBoundingClientRect().width,
        )
    JS);

    expect($barWidths)->not->toBeEmpty();
    expect(min($barWidths))->toBeGreaterThan(0);
    expect(max($barWidths) - min($barWidths))->toBeLessThan(1);

    $page->click($april.' a')->assertPathIs('/expenses')->assertQueryStringHas('month', '2025-04')->assertSee('₹5,432.10');
})->with(['desktop' => 1280, 'mobile' => 375]);

it('shows zero income and short monthly negative cashflow for recorded expenses', function () {
    $user = User::factory()->create();
    Expense::factory()->create(['amount' => 5000, 'date' => '2025-04-10']);
    $april = '[data-testid="cashflow-2025-04"]';

    loginAs($user->email)
        ->navigate('/reports?fy=2025')
        ->assertSeeIn($april.' [data-testid="income-amount"]', '₹0')
        ->assertSeeIn($april.' [data-testid="spent-amount"]', '₹5K')
        ->assertSeeIn($april, 'Net −₹5K')
        ->assertNoJavaScriptErrors();
});

it('opens the matching category and report period', function () {
    $user = User::factory()->create();
    $grocery = Category::factory()->create(['name' => 'Grocery']);
    Expense::factory()->create(['category_id' => $grocery->id, 'amount' => 3000, 'date' => '2026-04-10', 'note' => 'April groceries']);
    Expense::factory()->create(['category_id' => $grocery->id, 'amount' => 1000, 'date' => '2025-04-10', 'note' => 'Previous year groceries']);
    Expense::factory()->create(['amount' => 7000, 'date' => '2026-04-10', 'note' => 'Different category']);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSee('Spending by category')
        ->assertSeeIn('[data-testid="report-category-'.$grocery->id.'"]', '+₹2K')
        ->click('[data-testid="report-category-'.$grocery->id.'"] a')
        ->assertPathIs('/expenses')
        ->assertQueryStringHas('category_id', (string) $grocery->id)
        ->assertQueryStringHas('start_date', '2026-04-01')
        ->assertQueryStringHas('end_date', '2026-09-10')
        ->assertSee('April groceries')
        ->assertDontSee('Previous year groceries')
        ->assertDontSee('Different category')
        ->assertNoJavaScriptErrors();
});

it('keeps biggest expenses closed until selected', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Shopping']);
    $expense = Expense::factory()->create(['category_id' => $category->id, 'amount' => 84999, 'note' => 'New phone', 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertAttributeMissing('[data-testid="biggest-expenses"]', 'open')
        ->click('Biggest expenses')
        ->assertSeeIn('[data-testid="biggest-expenses"]', 'New phone')
        ->assertSeeIn('[data-testid="biggest-expenses"]', '₹85K')
        ->click('[data-testid="biggest-expenses"] a')
        ->assertPathIs('/expenses/'.$expense->id.'/edit');
});

it('filters by financial year via the url', function () {
    $user = User::factory()->create();
    Expense::factory()->create(['amount' => 7500, 'date' => '2024-06-15']);

    loginAs($user->email)
        ->navigate('/reports?fy=2024')
        ->assertSelected('fy-switcher', '2024')
        ->assertSee('₹7.5K');
});

it('shows an empty state when nothing is recorded', function () {
    $user = User::factory()->create();
    loginAs($user->email)->navigate('/reports')->assertSee('Nothing recorded in')->assertNoJavaScriptErrors();
});

it('shows completed month changes and opens their source expenses', function () {
    $user = User::factory()->create();
    $travel = Category::factory()->create(['name' => 'Travel']);
    $grocery = Category::factory()->create(['name' => 'Grocery']);
    Expense::factory()->create(['category_id' => $travel->id, 'amount' => 1000, 'date' => '2026-07-10']);
    Expense::factory()->create(['category_id' => $grocery->id, 'amount' => 1000, 'date' => '2026-07-10']);
    Expense::factory()->create(['category_id' => $travel->id, 'amount' => 4500, 'date' => '2026-08-10', 'note' => 'Train to Pune']);
    Expense::factory()->create(['category_id' => $grocery->id, 'amount' => 1500, 'date' => '2026-08-10', 'note' => 'August groceries']);
    Expense::factory()->create(['amount' => 900000, 'date' => '2026-09-10']);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSeeIn('[data-testid="monthly-comparison"]', 'Aug 2026 compared with Jul 2026')
        ->assertSeeIn('[data-testid="monthly-comparison"]', 'Recorded spending increased by ₹4K')
        ->assertSeeIn('[data-testid="monthly-comparison"]', '200% increase')
        ->assertSeeIn('[data-testid="monthly-comparison"]', '+₹3.5K')
        ->assertSeeIn('[data-testid="cashflow-2026-09"]', 'In progress')
        ->assertSeeIn('[data-testid="cashflow-summary"]', '₹1.6K/mo average')
        ->click('[data-testid="monthly-comparison"] li:first-child a')
        ->assertPathIs('/expenses')
        ->assertQueryStringHas('month', '2026-08')
        ->assertQueryStringHas('category_id', (string) $travel->id)
        ->assertSee('Train to Pune')
        ->assertDontSee('August groceries')
        ->click('@prev-month')
        ->assertQueryStringHas('month', '2026-07')
        ->assertQueryStringHas('category_id', (string) $travel->id)
        ->assertSee('₹1,000')
        ->click('Clear filters')
        ->assertQueryStringMissing('category_id')
        ->assertQueryStringMissing('month')
        ->assertSee('₹9,00,000')
        ->assertNoJavaScriptErrors();
});

it('shows other categories and expands the full category table on mobile', function () {
    $user = User::factory()->create();
    for ($index = 1; $index <= 10; $index++) {
        $category = Category::factory()->create(['name' => 'Category '.$index]);
        Expense::factory()->create(['category_id' => $category->id, 'amount' => $index * 1000, 'date' => Date::today()]);
    }

    $firstCategory = Category::query()->where('name', 'Category 1')->sole();

    $page = loginAs($user->email)
        ->resize(375, 900)
        ->navigate('/reports')
        ->assertSeeIn('[data-testid="other-categories"]', '₹3K')
        ->click('Show all 10 categories')
        ->assertSeeIn('[data-testid="report-category-'.$firstCategory->id.'"]', 'Category 1')
        ->assertSeeIn('[data-testid="report-category-'.$firstCategory->id.'"]', '₹1K')
        ->assertDontSee('Other categories')
        ->assertNoJavaScriptErrors();

    expect($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();
});

it('explains unavailable comparisons at the start of a financial year', function () {
    $this->travelTo(Date::parse('2026-04-10'));
    $user = User::factory()->create();
    Expense::factory()->create(['amount' => 5000, 'date' => Date::today()]);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSee('Monthly comparisons will appear after the first completed month.')
        ->assertDontSee('/mo average')
        ->assertSeeIn('[data-testid="cashflow-2026-04"]', 'In progress')
        ->assertSeeIn('[data-testid="annual-comparison"]', 'No expenses recorded')
        ->assertNoJavaScriptErrors();
});

it('shows small changes and long category names without losing mobile content', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => str_repeat('A', 100)]);
    Expense::factory()->create(['category_id' => $category->id, 'amount' => 100000, 'date' => '2025-08-10']);
    Expense::factory()->create(['category_id' => $category->id, 'amount' => 100000, 'date' => '2026-07-10']);
    Expense::factory()->create(['category_id' => $category->id, 'amount' => 100001, 'date' => '2026-08-10']);

    $page = loginAs($user->email)
        ->resize(375, 900)
        ->navigate('/reports')
        ->assertSeeIn('[data-testid="monthly-comparison"]', '+₹1')
        ->assertSeeIn('[data-testid="monthly-comparison"]', '<0.1% increase')
        ->assertSeeIn('[data-testid="category-comparison"]', $category->name)
        ->assertNoJavaScriptErrors();

    expect($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();
});

it('opens current month expenses only through the report cutoff date', function () {
    $user = User::factory()->create();
    Expense::factory()->create(['amount' => 100, 'date' => Date::today(), 'note' => 'Recorded today']);
    Expense::factory()->create(['amount' => 900, 'date' => Date::today()->addDays(10), 'note' => 'Future expense']);

    loginAs($user->email)
        ->navigate('/reports')
        ->assertSeeIn('[data-testid="cashflow-2026-09"] [data-testid="spent-amount"]', '₹100')
        ->click('[data-testid="cashflow-2026-09"] a')
        ->assertQueryStringHas('start_date', '2026-09-01')
        ->assertQueryStringHas('end_date', '2026-09-10')
        ->assertSee('Recorded today')
        ->assertDontSee('Future expense')
        ->assertSee('₹100')
        ->assertNoJavaScriptErrors();
});
