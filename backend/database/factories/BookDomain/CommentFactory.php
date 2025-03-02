<?php

namespace Database\Factories\BookDomain;

use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookComment>
 */
class CommentFactory extends Factory
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
        $book = Book::findOrFail($random_book_id);

        return [
            'user_id' => $random_user_id,
            'book_id' => $random_book_id,
            'comment' => fake()->realText(15),
            'is_reviewer_comment' => $book->user_id === $random_user_id ? 1 : 0,
            'comment_likes' => 0,
        ];
    }
}
