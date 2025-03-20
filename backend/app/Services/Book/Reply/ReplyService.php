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

        $replies = Reply::where('comment_id', $comment_id)->get();

        $user_likes = $replies[0]->likes()->where('user_id', $auth_id)
            ->whereIn('reply_id', $replies->pluck('id'))
            ->pluck('reply_id')
            ->flip()
            ->all();

        return $replies->map(function ($reply) use ($auth_id, $user_likes) {
            return [
                'id' => $reply->id,
                'user_name' => $reply->user->name,
                'user_image_url' => $reply->user->image_url,
                'content' => $reply->content,
                'likes' => $reply->likes,
                'is_reviewer' => $reply->is_reviewer_reply,
                'is_your_reply' => $reply->user_id === $auth_id,
                'is_likes_reply' => array_key_exists($reply->id, $user_likes) ? true : false,
            ];
        })->all();
    }
}
