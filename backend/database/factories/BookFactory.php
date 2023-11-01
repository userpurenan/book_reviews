<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->realText($maxNbChars = 15),
            'url' => $this->faker->email,
            'detail' => $this->faker->realText($maxNbChars = 15),
            'review' => $this->faker->realText($maxNbChars = 35),
            'reviewer' => $this->faker->name(),
            'token' => Str::random(220)
        ];
    }
}
