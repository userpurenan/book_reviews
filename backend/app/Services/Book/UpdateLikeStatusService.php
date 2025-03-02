<?php

declare(strict_types=1);

namespace App\Services\Book;

use Illuminate\Support\Facades\Auth;
use App\Models\UserDomain\UserReplyLikes;
use App\Models\UserDomain\UserReviewLikes;
use App\Models\UserDomain\UserCommentLikes;

class UpdateLikeStatusService
{
    public function updateBookReviewLikeStatus($book, int $likes_count_change)
    {
        if($likes_count_change === 1) {
            //いいねしたことを保持するためにデータベースにユーザーと書籍レビューのidを追加する
            UserReviewLikes::create([
                'user_id' => Auth::id(),
                'book_id' => $book->id
            ]);
        } else {
            UserReviewLikes::where('user_id', Auth::id())->where('book_id', $book->id)->delete();
        }
    }

    public function updateCommentLikeStatus($comment, int $likes_count_change)
    {
        if($likes_count_change === 1) {
            //いいねしたことを保持するためにデータベースにユーザーとコメントのidを追加する
            UserCommentLikes::create([
                'user_id' => Auth::id(),
                'comment_id' => $comment->id
            ]);
        } else {
            UserCommentLikes::where('user_id', Auth::id())->where('comment_id', $comment->id)->delete();
        }
    }

    public function updateReplyLikeStatus($reply, int $likes_count_change)
    {
        if($likes_count_change === 1) {
            //いいねしたことを保持するためにデータベースにユーザーとリプライのidを追加する
            UserReplyLikes::create([
                'user_id' => Auth::id(),
                'reply_id' => $reply->id
            ]);
        } else {
            UserReplyLikes::where('user_id', Auth::id())->where('reply_id', $reply->id)->delete();
        }
    }
}
