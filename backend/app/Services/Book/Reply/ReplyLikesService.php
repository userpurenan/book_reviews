<?php

declare(strict_types=1);

namespace App\Services\Book\Reply;

use App\Models\BookDomain\Reply;
use Illuminate\Support\Facades\DB;
use App\Services\Book\UpdateLikeStatusService;

class ReplyLikesService
{
    private UpdateLikeStatusService $update_like_status;

    public function __construct(UpdateLikeStatusService $update_like_status)
    {
        $this->update_like_status = $update_like_status;
    }

    // これだとコントローラーの処理が重複してしまうので、リクエストの処理はコントローラーで行うようにする
    public function updateLikes(int $reply_id, int $likes): array
    {
        $reply = Reply::findOrFail($reply_id);
        $new_likes_count = $reply->reply_likes + $likes;

        if($new_likes_count < 0) {
            return [ 'error' => 'いいねは0未満にはできません' ];
        }

        $retryTimes = 3;
        DB::transaction(function () use ($reply, $new_likes_count, $likes) {
            $reply->update(['reply_likes' => $new_likes_count ]);

            // 可読性向上の目的で、いいねの状態を管理するテーブル操作はサービスクラスに切り出した
            $this->update_like_status->updateReplyLikeStatus($reply, $likes);
        }, $retryTimes);

        return [ 'reply_likes' => $new_likes_count ];
    }
}
