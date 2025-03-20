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
    public function updateLikes(int $comment_id, int $likes): array
    {
        $user = User::findOrFail(Auth::id());
        $comment = Comment::findOrFail($comment_id);
        $new_likes_count = $comment->likes + $likes;

        if($new_likes_count < 0) {
            return [ 'error' => 'いいねは0未満にはできません' ];
        }

        $retryTimes = 3;
        DB::transaction(function () use ($user, $comment, $new_likes_count, $likes) {
            $comment->update(['likes' => $new_likes_count ]);

            // 可読性向上の目的で、いいねの状態を管理するテーブル操作はサービスクラスに切り出した
            // $this->update_like_status->updateCommentLikeStatus($comment, $likes);
            if($likes === 1) {
                $user->comment_likes()->attach($comment->id);
            } else {
                $user->comment_likes()->detach($comment->id);
            }
        }, $retryTimes);

        return [ 'likes' => $new_likes_count ];
    }
}
