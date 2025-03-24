<?php

declare(strict_types=1);

namespace App\Services\Book\Reply;

use App\Models\UserDomain\User;
use App\Models\BookDomain\Reply;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReplyLikesService
{
    // これだとコントローラーの処理が重複してしまうので、リクエストの処理はコントローラーで行うようにする
    public function incrementLikes(int $reply_id): array
    {
        $user = User::findOrFail(Auth::id());
        $reply = Reply::findOrFail($reply_id);
        $increment_likes_result = $reply->likes + 1;

        $retryTimes = 3;
        DB::transaction(function () use ($user, $reply, $increment_likes_result) {
            $reply->update(['likes' => $increment_likes_result ]);
            $user->reply_likes()->attach($reply->id);
        }, $retryTimes);

        return [ 'likes' => $increment_likes_result ];
    }

    public function decrementLikes(int $reply_id): array
    {
        $user = User::findOrFail(Auth::id());
        $reply = Reply::findOrFail($reply_id);
        $decrement_likes_result = $reply->likes - 1;

        if ($decrement_likes_result < 0) 
        {
            return ['error' => 'いいねは0未満に設定できません'];
        }

        $retryTimes = 3;
        DB::transaction(function () use ($user, $reply, $decrement_likes_result) {
            $reply->update(['likes' => $decrement_likes_result ]);
            $user->reply_likes()->detach($reply->id);
        }, $retryTimes);

        return [ 'likes' => $decrement_likes_result ];
    }
}
