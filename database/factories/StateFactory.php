<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class StateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $this->faker = \Faker\Factory::create('ru_RU');

        return [
            'title' => $this->faker->unique()->city('ru_RU'),
            'country_id' => 1,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
}
