<?php

declare(strict_types=1);

namespace App\Services\Book\Reply;

use App\Models\BookDomain\Reply;
use Illuminate\Support\Facades\Auth;
use App\Models\UserDomain\UserReplyLikes;

class ReplyService
{
    public function setReply(int $comment_id): array
    {
        $auth_id = Auth::id();

        $comment_reply = Reply::with('user')->where('comment_id', $comment_id)->get();

        $user_likes = UserReplyLikes::where('user_id', $auth_id)
            ->whereIn('reply_id', $comment_reply->pluck('id'))
            ->pluck('reply_id')
            ->flip()
            ->all();

        return $comment_reply->map(function ($reply) use ($auth_id, $user_likes) {
            return [
                'id' => $reply->id,
                'user_name' => $reply->user->name,
                'user_image_url' => $reply->user->image_url,
                'reply' => $reply->content,
                'reply_likes' => $reply->likes,
                'is_reviewer' => $reply->is_reviewer_reply,
                'is_your_reply' => $reply->user_id === $auth_id,
                'is_likes_reply' => array_key_exists($reply->id, $user_likes) ? true : false,
            ];
        })->all();
    }
}
