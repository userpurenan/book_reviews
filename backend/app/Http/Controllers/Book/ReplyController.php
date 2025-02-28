<?php

namespace App\Http\Controllers\Book;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Book\BookComment;
use Illuminate\Support\Facades\Auth;
use App\Models\Book\Reply;
use App\Services\Book\Reply\ReplyService;
use App\Services\Book\Reply\ReplyLikesService;

use Illuminate\Http\JsonResponse;

class ReplyController extends Controller
{
    public function fetchReply(int $comment_id, ReplyService $reply_service): JsonResponse
    {
        $review_comment_array = $reply_service->setReply($comment_id);

        return response()->json($review_comment_array, 200);
    }

    public function createReply(Request $request, int $comment_id): JsonResponse
    {
        $user_id = Auth::id();
        $comment = BookComment::findOrFail($comment_id);

        //is_reviewer_replyはtrueかfalseをセットしているが、MySQLの使用上tinyint(1)として扱われるのでデータベースには１か０がセットされる
        $books_review_reply = Reply::create([
                                    'user_id' => $user_id,
                                    'comment_id' => $comment_id,
                                    'reply' => $request->input('reply'),
                                    'is_reviewer_reply' => $comment->user_id === $user_id ? 1 : 0,
                                    'reply_likes' => 0
                                ]);

        return response()->json([
                    'user_name' => $books_review_reply->user->name,
                    'user_image_url' => $books_review_reply->user->image_url,
                    'reply' => $books_review_reply->reply,
                    'reply_likes' => $books_review_reply->reply_likes
                ], 201);
    }

    public function updateReply(Request $request, int $comment_id, int $reply_id): JsonResponse
    {
        $book_review_reply = Reply::findOrFail($reply_id);

        $book_review_reply->update([ 'reply' => $request->input('reply') ]);

        return response()->json([
            'user_name' => $book_review_reply->user->name,
            'user_image_url' => $book_review_reply->user->image_url,
            'reply' => $book_review_reply->reply,
            'reply_likes' => $book_review_reply->reply_likes
        ], 201);
    }

    public function deleteReply(int $book_id, int $reply_id): JsonResponse
    {
        Reply::findOrFail($reply_id)->delete();

        return response()->json([ 'message' => 'delete success' ], 200);
    }

    public function updateLikes(Request $request, int $comment_id, int $reply_id, ReplyLikesService $comment_like): JsonResponse
    {
        $likes = (int) $request->input('likes');

        $update_likes_result = $comment_like->updateLikes($reply_id, $likes);

        if(isset($update_likes_result['error'])) {
            return response()->json($update_likes_result, 500);
        }

        return response()->json($update_likes_result, 200);
    }
}
