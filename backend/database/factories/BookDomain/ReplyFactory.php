<?php

namespace Database\Factories\BookDomain;

use Illuminate\Support\Str;
use App\Models\UserDomain\User;
use App\Models\BookDomain\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $random_comment_id = Comment::inRandomOrder()->first()->id;
        $comment = Comment::findOrFail($random_comment_id);

        return [
            'user_id' => $random_user_id,
            'comment_id' => $comment->id,
            'content' => Str::random(10),
            'is_reviewer_reply' => $comment->user_id === $random_user_id ? 1 : 0,
            'likes' => 0
        ];
    }
}
