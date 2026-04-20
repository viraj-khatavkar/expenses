<?php

namespace Database\Factories;

use App\Models\Income;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<Income>
 */
class IncomeFactory extends Factory
{
    protected $model = Income::class;

    public function definition(): array
    {
        return [
            'date' => Date::now(),
            'amount' => fake()->numberBetween(1000, 100000),
            'source_id' => Source::factory(),
        ];
    }
}
