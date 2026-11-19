<?php

namespace Database\Factories;

use App\Models\InvestmentMonth;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<InvestmentMonth>
 */
class InvestmentMonthFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'year' => Date::today()->year,
            'month' => fake()->numberBetween(1, 12),
            'planned' => fake()->numberBetween(10, 100) * 1000,
        ];
    }
}
