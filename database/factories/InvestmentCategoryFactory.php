<?php

namespace Database\Factories;

use App\Models\InvestmentCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<InvestmentCategory> */
class InvestmentCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'name_key' => fn (array $attributes): string => Str::lower($attributes['name']),
        ];
    }
}
