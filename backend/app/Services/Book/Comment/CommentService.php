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
        $comments = Comment::GetBookComment($book_id)
            ->offset($number)
            ->limit(10)
            ->orderBy('id', 'desc')
            ->get();

        // ユーザーのいいね情報を一括で取得
        $user_likes = $comments[0]->likes()->where('user_id', $auth_id)
            ->whereIn('comment_id', $comments->pluck('id'))
            ->pluck('comment_id')
            ->flip()
            ->all();

        return $comments->map(function ($comment) use ($auth_id, $user_likes) {
            return [
                'id' => $comment->id,
                'user_name' => $comment->user->name,
                'user_image_url' => $comment->user->image_url,
                'content' => $comment->content,
                'likes' => $comment->likes,
                'is_reviewer' => $comment->is_reviewer_comment,
                'is_your_comment' => $comment->user_id === $auth_id,
                'is_likes_comment' => array_key_exists($comment->id, $user_likes) ? true : false,
            ];
        })->all();
    }
}
