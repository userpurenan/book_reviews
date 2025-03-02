<?php

declare(strict_types=1);

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Controller;
use App\Models\Book\Book;
use App\Models\Book\BookComment;
use App\Services\Book\Comment\CommentService;
use App\Services\Book\Comment\CommentLikesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class BookCommentController extends Controller
{
    public function getComment(Request $request, CommentService $comment_service, int $book_id): JsonResponse
    {
        $number = (int) $request->query('comment_offset', $default = 0);

        // コメントを配列に詰める処理をサービスクラスに切り出した
        $review_comment_array = $comment_service->setComment($book_id, $number);

        return response()->json($review_comment_array, 200);
    }

    public function createComment(Request $request, int $book_id): JsonResponse
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
                ], 200);
    }

    public function editComment(Request $request, BookComment $book_comment, int $book_id, int $comment_id): JsonResponse
    {
        Gate::authorize('auth_comment', $book_comment);

        $book_review_comment = $book_comment->findOrFail($comment_id);

        $book_review_comment->update([ 'comment' => $request->input('comment') ]);

        return response()->json([
            'user_name' => $book_review_comment->user->name,
            'user_image_url' => $book_review_comment->user->image_url,
            'comment' => $book_review_comment->comment,
            'comment_likes' => $book_review_comment->comment_likes
        ], 200);
    }

    public function deleteComment(BookComment $book_comment, int $book_id, int $comment_id): JsonResponse
    {
        Gate::authorize('auth_comment', $book_comment);

        BookComment::findOrFail($comment_id)->delete();

        return response()->json([
            'message' => 'コメントの削除に成功しました'
        ], 200);
    }

    public function updateLikes(Request $request, CommentLikesService $comment_like): JsonResponse
    {
        $comment_id = (int) $request->input('comment_id');
        $likes = (int) $request->input('likes');

        $update_likes_result = $comment_like->updateLikes($comment_id, $likes);

        if(isset($update_likes_result['error'])) {
            return response()->json($update_likes_result, 500);
        }

        return response()->json($update_likes_result, 200);
    }
}
