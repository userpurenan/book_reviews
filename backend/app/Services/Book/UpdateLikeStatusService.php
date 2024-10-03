<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Models\UserCommentLikes;
use App\Models\UserReviewLikes;
use Illuminate\Support\Facades\Auth;

class UpdateLikeStatusService
{
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
}
