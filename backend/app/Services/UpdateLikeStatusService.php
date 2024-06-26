<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserCommentLikes;
use App\Models\UserReviewLikes;
use Illuminate\Support\Facades\Auth;

class UpdateLikeStatusService
{
    public static function updateCommentLikeStatus($comment, int $likes_count_change)
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

    public static function updateBookReviewLikeStatus($book, int $likes_count_change)
    {
        $is_review_likes = null;
        if($likes_count_change === 1) {
            //いいねしたことを保持するためにデータベースにユーザーと書籍レビューのidを追加する
            $is_review_likes = UserReviewLikes::create([
                'user_id' => Auth::id(),
                'book_id' => $book->id
            ]);
        } else {
            UserReviewLikes::where('user_id', Auth::id())->where('book_id', $book->id)->delete();
        }

        return $is_review_likes;
    }
}
