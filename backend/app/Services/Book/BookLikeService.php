<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookLikeService
{
    public function updateLikes(int $book_id, int $likes)
    {
        $user = User::findOrFail(Auth::id());
        $book = Book::findOrFail($book_id);
        $new_likes_count = $book->likes + $likes;

        if($new_likes_count < 0) {
            // controllerで、「返り値の配列のキーにerrorがあったら500エラーを返す」という処理を書いているので、
            // ここでは、errorをキーに取った配列（＋エラーメッセージ）を返す
            return [ 'error' => 'いいねは0未満にはできません' ];
        }

        $retryTimes = 3;
        DB::transaction(function () use ($user, $book, $new_likes_count, $likes) {
            $book->update(['likes' => $new_likes_count ]);

            // 可読性向上の目的で、いいねの状態を管理するテーブル操作はサービスクラスに切り出した
            if($likes === 1) {
                $user->book_likes()->attach($book->id);
            } else {
                $user->book_likes()->detach($book->id);
            }
        }, $retryTimes);

        return [ 'likes' => $book->likes ];
    }
}
