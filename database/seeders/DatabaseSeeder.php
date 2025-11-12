<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
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

        User::factory()->create([
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
    }
}
