<?php

declare(strict_types=1);

namespace App\Http\Controllers\BookDomain;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\BookDomain\Reply;
use Illuminate\Http\JsonResponse;
use App\Models\BookDomain\Comment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Gate;
use App\Services\Book\Reply\ReplyService;
use App\Services\Book\Reply\ReplyLikesService;

class ReplyController extends Controller
{
    public function index(int $comment_id, ReplyService $reply_service): JsonResponse
    {
        $review_comment_array = $reply_service->setReply($comment_id);

        return response()->json($review_comment_array, 200);
    }

    public function store(Request $request, int $comment_id): JsonResponse
    {
        $user_id = Auth::id();
        $comment = Comment::findOrFail($comment_id);

        //is_reviewer_replyはtrueかfalseをセットしているが、MySQLの使用上tinyint(1)として扱われるのでデータベースには１か０がセットされる
        $books_review_reply = Reply::create([
                                    'user_id' => $user_id,
                                    'comment_id' => $comment_id,
                                    'content' => $request->input('content'),
                                    'is_reviewer_reply' => $comment->user_id === $user_id ? 1 : 0,
                                    'likes' => 0
                                ]);

        return response()->json([
                    'user_name' => $books_review_reply->user->name,
                    'user_image_url' => $books_review_reply->user->image_url,
                    'content' => $books_review_reply->content,
                    'likes' => $books_review_reply->likes
                ], 201);
    }

    public function update(Request $request, Reply $reply, int $reply_id): JsonResponse
    {
        $comment_reply = $reply->findOrFail($reply_id);

        Gate::authorize('auth_reply', $comment_reply);

        $comment_reply->update([ 'content' => $request->input('content') ]);

        return response()->json([
            'user_name' => $comment_reply->user->name,
            'user_image_url' => $comment_reply->user->image_url,
            'content' => $comment_reply->content,
            'likes' => $comment_reply->likes
        ], 201);
    }

    public function destroy(Reply $reply, int $reply_id): Response
    {
        $comment_reply = $reply->findOrFail($reply_id);

        Gate::authorize('auth_reply', $comment_reply);

        $comment_reply->delete();

        return response()->noContent();
    }

    public function incrementLikes(int $reply_id, ReplyLikesService $reply_like): JsonResponse
    {
        $increment_likes_result = $reply_like->incrementLikes($reply_id);

        if(isset($increment_likes_result['error'])) {
            return response()->json($increment_likes_result, 500);
        }

        return response()->json($increment_likes_result, 200);
    }

    public function decrementLikes(int $reply_id, ReplyLikesService $reply_like): JsonResponse
    {
        $decrement_likes_result = $reply_like->decrementLikes($reply_id);

        if(isset($decrement_likes_result['error'])) {
            return response()->json($decrement_likes_result, 500);
        }

        return response()->json($decrement_likes_result, 200);
    }
}
