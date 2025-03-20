<?php

declare(strict_types=1);

namespace App\Services\Book\Reply;

use App\Models\UserDomain\User;
use App\Models\BookDomain\Reply;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReplyLikesService
{
    // これだとコントローラーの処理が重複してしまうので、リクエストの処理はコントローラーで行うようにする
    public function updateLikes(int $reply_id, int $likes): array
    {
        $user = User::findOrFail(Auth::id());
        $reply = Reply::findOrFail($reply_id);
        $new_likes_count = $reply->likes + $likes;

        if($new_likes_count < 0) {
            return [ 'error' => 'いいねは0未満にはできません' ];
        }

        $retryTimes = 3;
        DB::transaction(function () use ($user, $reply, $new_likes_count, $likes) {
            $reply->update(['likes' => $new_likes_count ]);

            // 可読性向上の目的で、いいねの状態を管理するテーブル操作はサービスクラスに切り出した
            // $this->update_like_status->updateReplyLikeStatus($reply, $likes);
            if($likes === 1) {
                $user->reply_likes()->attach($reply->id);
            } else {
                $user->reply_likes()->detach($reply->id);
            }
        }, $retryTimes);

        return [ 'likes' => $new_likes_count ];
    }
}
