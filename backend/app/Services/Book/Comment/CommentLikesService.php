<?php

declare(strict_types=1);

namespace App\Services\Book\Comment;

use App\Models\BookDomain\Comment;
use App\Models\UserDomain\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CommentLikesService
{
    // これだとコントローラーの処理が重複してしまうので、リクエストの処理はコントローラーで行うようにする
    public function incrementLikes(int $comment_id): array
    {
        $user = User::findOrFail(Auth::id());
        $comment = Comment::findOrFail($comment_id);
        $increment_likes_result = $comment->likes + 1;

        $retryTimes = 3;
        DB::transaction(function () use ($user, $comment, $increment_likes_result) {
            $comment->update(['likes' => $increment_likes_result ]);
            $user->comment_likes()->attach($comment->id);
        }, $retryTimes);

        return [ 'likes' => $increment_likes_result ];
    }

    public function decrementLikes(int $comment_id)
    {
        $user = User::findOrFail(Auth::id());
        $comment = Comment::findOrFail($comment_id);
        $decrement_likes_result = $comment->likes - 1;

        if($decrement_likes_result < 0) {
            return [ 'error' => 'いいねは0未満にはできません' ];
        }

        $retryTimes = 3;
        DB::transaction(function () use ($user, $comment, $decrement_likes_result) {
            $comment->update(['likes' => $decrement_likes_result ]);
            $user->comment_likes()->detach($comment->id);
        }, $retryTimes);

        return [ 'likes' => $decrement_likes_result ];
    }
}
