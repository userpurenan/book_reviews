<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BookComment;
use Illuminate\Support\Facades\DB;
use App\Services\UpdateLikeStatusService;

class UpdateLikesService
{
    private UpdateLikeStatusService $update_like_status;

    public function __construct(UpdateLikeStatusService $update_like_status)
    {
        $this->update_like_status = $update_like_status;
    }

    public function updateLikes($request): int
    {
        $comment = BookComment::findOrFail($request->input('comment_id'));
        $likes_count_change = (int) $request->input('likes'); //「1」か「-1」が渡される
        $new_likes_count = $comment->comment_likes + $likes_count_change;

        if($new_likes_count < 0) {
            return response()->json([
                'error' => 'いいねは0未満にはできません'
            ], 500);
        }

        DB::transaction(function () use ($comment, $new_likes_count, $likes_count_change) {
            $comment->update(['comment_likes' => $new_likes_count ]);

            // 可読性向上の目的で、いいねの状態を管理するテーブル操作はサービスクラスに切り出した
            $this->update_like_status->updateCommentLikeStatus($comment, $likes_count_change);
        });

        return $new_likes_count;
    }
}
