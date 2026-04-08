<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'title' => fake()->company(),
            'amount' => fake()->numberBetween(100, 5000),
            'currency' => fake()->randomElement(['INR', 'USD']),
            'frequency' => fake()->randomElement(['monthly', 'quarterly', 'yearly']),
        ];
    }
}
