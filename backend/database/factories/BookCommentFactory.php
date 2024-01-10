<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookComment>
 */
class BookCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_user_id = User::inRandomOrder()->first()->id;
        $random_book_id = Book::inRandomOrder()->first()->id;
        return [
            'user_id' => $random_user_id,
            'book_id' => $random_book_id,
            'comment' => fake()->realText(15),
            'comment_likes' => 0,
        ];
    }
}
