<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'date' => Date::now(),
            'amount' => fake()->numberBetween(1, 100),
            'category_id' => Category::factory(),
        ];
    }
}
