<?php

declare(strict_types=1);

namespace App\Services\Book\Comment;

use App\Models\BookDomain\Comment;
use Illuminate\Support\Facades\Auth;
use App\Models\UserDomain\UserCommentLikes;

class CommentService
{
    public function setComment(int $book_id, int $number): array
    {
        $auth_id = Auth::id();

        // GetBookCommentはモデルに定義されているスコープ。レビューに対するコメントを10件ずつ取得してくる。
        $books_review_comment = Comment::GetBookComment($book_id, $number)
            ->offset($number)
            ->limit(10)
            ->orderBy('id', 'desc')
            ->get();

        // ユーザーのいいね情報を一括で取得
        $user_likes = UserCommentLikes::where('user_id', $auth_id)
            ->whereIn('comment_id', $books_review_comment->pluck('id'))
            ->pluck('comment_id')
            ->flip()
            ->all();

        return $books_review_comment->map(function ($review_comment) use ($auth_id, $user_likes) {
            return [
                'id' => $review_comment->id,
                'user_name' => $review_comment->user->name,
                'user_image_url' => $review_comment->user->image_url,
                'content' => $review_comment->content,
                'likes' => $review_comment->likes,
                'is_reviewer' => $review_comment->is_reviewer_comment,
                'is_your_comment' => $review_comment->user_id === $auth_id,
                'is_likes_comment' => array_key_exists($review_comment->id, $user_likes) ? true : false,
            ];
        })->all();
    }
}
