<?php

declare(strict_types=1);

namespace App\Http\Controllers\BookDomain;

use Illuminate\Http\Request;
use App\Models\BookDomain\Book;
use Illuminate\Http\JsonResponse;
use App\Models\BookDomain\Comment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Services\Book\Comment\CommentService;
use App\Services\Book\Comment\CommentLikesService;

class CommentController extends Controller
{
    public function index(Request $request, CommentService $comment_service, int $book_id): JsonResponse
    {
        $number = (int) $request->query('comment_offset', $default = 0);

        // コメントを配列に詰める処理をサービスクラスに切り出した
        $review_comment_array = $comment_service->setComment($book_id, $number);

        return response()->json($review_comment_array, 200);
    }

    public function store(Request $request, int $book_id): JsonResponse
    {
        $user_id = Auth::id();
        $book = Book::findOrFail($book_id);

        //is_reviewer_commentはtrueかfalseをセットしているが、MySQLの使用上tinyint(1)として扱われるのでデータベースには１か０がセットされる
        $books_review_comment = Comment::create([
                                    'user_id' => $user_id,
                                    'book_id' => $book_id,
                                    'content' => $request->input('content'),
                                    'is_reviewer_comment' => $book->user_id === $user_id ? 1 : 0,
                                    'likes' => 0
                                ]);

        return response()->json([
                    'user_name' => $books_review_comment->user->name,
                    'user_image_url' => $books_review_comment->user->image_url,
                    'content' => $books_review_comment->content,
                    'likes' => $books_review_comment->likes
                ], 200);
    }

    public function update(Request $request, Comment $comment, int $comment_id): JsonResponse
    {
        $review_comment = $comment->findOrFail($comment_id);

        Gate::authorize('auth_comment', $review_comment);

        $review_comment->update([ 'content' => $request->input('content') ]);

        return response()->json([
            'user_name' => $review_comment->user->name,
            'user_image_url' => $review_comment->user->image_url,
            'content' => $review_comment->content,
            'likes' => $review_comment->likes
        ], 200);
    }

    public function destroy(Comment $comment, int $comment_id): JsonResponse
    {
        $review_comment = $comment->findOrFail($comment_id);

        Gate::authorize('auth_comment', $review_comment);

        $review_comment->delete();

        return response()->json([
            'message' => 'コメントの削除に成功しました'
        ], 200);
    }

    public function incrementLikes(int $comment_id, CommentLikesService $comment_like): JsonResponse
    {
        $increment_likes_result = $comment_like->incrementLikes($comment_id);

        return response()->json($increment_likes_result, 200);
    }

    public function decrementLikes(int $comment_id, CommentLikesService $comment_like): JsonResponse
    {
        $decrement_likes_result = $comment_like->decrementLikes($comment_id);

        if(isset($decrement_likes_result['error'])) {
            return response()->json($decrement_likes_result, 500);
        }

        return response()->json($decrement_likes_result, 200);
    }
}
