<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\InvestmentCategory;
use App\Models\InvestmentMonth;
use App\Models\InvestmentTransaction;
use App\Models\Source;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Viraj Khatavkar',
            'email' => 'viraj.2438@gmail.com',
        ]);

        $categories = [
            'Food Delivery',
            'Dining Out',
            'Grocery',
            'Starbucks',
            'Healthcare',
            'Fashion',
            'Grooming',
            'Subscriptions',
            'Vacation',
        ];

        foreach ($categories as $category) {
            Category::factory()->create(['name' => $category]);
        }

        Expense::factory()->count(100)->create();

        $sources = [
            'Salary',
            'Freelance',
            'Investments',
            'Bonus',
            'Interest',
        ];

        foreach ($sources as $source) {
            Source::factory()->create(['name' => $source]);
        }

        Income::factory()->count(20)->create();

        InvestmentMonth::factory()->for($user)
            ->forEachSequence(['month' => 1], ['month' => 2], ['month' => 3])
            ->create();
        $fund = InvestmentCategory::forName($user, 'Mutual funds');
        InvestmentTransaction::factory()->for($fund, 'category')->create();
    }
}
