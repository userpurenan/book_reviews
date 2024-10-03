<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Models\UserCommentLikes;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    public function setComment($books_review_comment)
    {
        $review_comment_array = [];
        foreach ($books_review_comment as $review_comment) {
            $is_your_comment = false;
            $is_comment_likes = false;
            if($review_comment->user_id === Auth::id()) { //認証ユーザーが書いたコメントかを判定
                $is_your_comment = true;
            }

            //　コメントに対するいいねの状態を保存するテーブルを参照する
            if(UserCommentLikes::where('user_id', Auth::id())->where('comment_id', $review_comment->id)->first()) {
                $is_comment_likes = true;
            }

            $review_comment_array[] = [
                'id' => $review_comment->id,
                'user_name' => $review_comment->user->name,
                'user_image_url' => $review_comment->user->image_url,
                'comment' => $review_comment->comment,
                'comment_likes' => $review_comment->comment_likes,
                'is_reviewer' => $review_comment->is_reviewer_comment, //MySQLのboolean型からデータを引っ張ってきているのでレスポンスが１(true)または０(false)になる
                'is_your_comment' => $is_your_comment,
                'is_likes_comment' => $is_comment_likes
            ];
        }

        return $review_comment_array;
    }
}
