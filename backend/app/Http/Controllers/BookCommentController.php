<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UpdateLikeStatusService;
use App\Models\Book;
use App\Models\BookComment;
use App\Models\UserCommentLikes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookCommentController extends Controller
{
    public function createComment(Request $request, int $book_id)
    {
        $user_id = Auth::id();
        $book = Book::findOrFail($book_id);

        //is_reviewer_commentはtrueかfalseをセットしているが、MySQLの使用上tinyint(1)として扱われるのでデータベースには１か０がセットされる
        $books_review_comment = BookComment::create([
                                    'user_id' => $user_id,
                                    'book_id' => $book_id,
                                    'comment' => $request->input('comment'),
                                    'is_reviewer_comment' => $book->user_id === $user_id ? 1 : 0,
                                    'comment_likes' => 0
                                ]);

        return response()->json([
                    'user_name' => $books_review_comment->user->name,
                    'user_image_url' => $books_review_comment->user->image_url,
                    'comment' => $books_review_comment->comment,
                    'comment_likes' => $books_review_comment->comment_likes
                ], 200, []);
    }

    public function editComment(Request $request, int $book_id)
    {
        $book_review_comment = BookComment::findOrFail($book_id);

        $book_review_comment->update([ 'comment' => $request->input('comment') ]);

        return response()->json([
            'user_name' => $book_review_comment->user->name,
            'user_image_url' => $book_review_comment->user->image_url,
            'comment' => $book_review_comment->comment,
            'comment_likes' => $book_review_comment->comment_likes
        ], 200, []);
    }

    public function updateLikes(Request $request)
    {
        $likes_count_change = (int) $request->input('likes'); //「1」か「-1」が渡される
        $comment = BookComment::findOrFail($request->input('comment_id'));

        $comment_likes_count = $comment->comment_likes + $likes_count_change;
        $comment->update(['comment_likes' => $comment_likes_count ]);

        // 可読性向上の目的で、いいねの状態を管理するテーブル操作はサービスクラスに切り出した
        UpdateLikeStatusService::updateCommentLikeStatus($comment, $likes_count_change);

        return response()->json([
            'comment_likes' => $comment->comment_likes
        ], 200);
    }

    public function getComment(Request $request, int $book_id)
    {
        $number = $request->query('comment_offset', $default = 0);

        // GetBookCommentはモデルに定義されているスコープ。レビューに対するコメントを10件ずつ取得してくる。
        $books_review_comment = BookComment::GetBookComment($book_id, $number)->offset($number)->limit(10)->orderBy('id', 'desc')->get();

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

        return response()->json($review_comment_array, 200, []);
    }

    public function deleteComment(int $book_id)
    {
        BookComment::findOrFail($book_id)->delete();
    }
}
