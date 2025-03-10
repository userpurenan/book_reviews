<?php

namespace Database\Factories\BookDomain;

use App\Models\UserDomain\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $randomUserId = User::inRandomOrder()->first()->id;
        return [
            // 'title' => fake()>realText($maxNbChars = 15),
            'title' => "ワンピース",
            'user_id' => $randomUserId,
            'url' => fake()->url(),
            'detail' => fake()->realText(15),
            'review' => fake()->realText(35),
            'reviewer' => User::findOrFail($randomUserId)->name,
            'spoiler' => rand(0, 1)
        ];
    }
}
