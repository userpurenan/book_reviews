<?php

namespace Database\Factories\Book;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use app\Models\User\User;
use App\Models\Book\BookComment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_user_id = User::inRandomOrder()->first()->id;
        $random_comment_id = BookComment::inRandomOrder()->first()->id;
        $comment = BookComment::findOrFail($random_comment_id);

        return [
            'user_id' => $random_user_id,
            'comment_id' => $comment->id,
            'reply' => Str::random(10),
            'is_reviewer_reply' => $comment->user_id === $random_user_id ? 1 : 0,
            'reply_likes' => 0
        ];
    }
}
