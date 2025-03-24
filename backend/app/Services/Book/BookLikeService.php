<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookLikeService
{
    public function incrementLikes(int $book_id)
    {
        $user = User::findOrFail(Auth::id());
        $book = Book::findOrFail($book_id);
        $increment_likes_result = $book->likes + 1;

        $retryTimes = 3;
        DB::transaction(function () use ($user, $book, $increment_likes_result) {
            $book->update(['likes' => $increment_likes_result ]);
            $user->book_likes()->attach($book->id);
        }, $retryTimes);

        return [ 'likes' => $book->likes ];
    }

    public function decrementLikes(int $book_id)
    {
        $user = User::findOrFail(Auth::id());
        $book = Book::findOrFail($book_id);
        $decrement_likes_result = $book->likes - 1;

        if($decrement_likes_result < 0) {
            // controllerで、「返り値の配列のキーにerrorがあったら500エラーを返す」という処理を書いているので、
            // ここでは、errorをキーに取った配列（＋エラーメッセージ）を返す
            return [ 'error' => 'いいねは0未満にはできません' ];
        }

        $retryTimes = 3;
        DB::transaction(function () use ($user, $book, $decrement_likes_result) {
            $book->update(['likes' => $decrement_likes_result ]);
            $user->book_likes()->detach($book->id);
        }, $retryTimes);

        return [ 'likes' => $book->likes ];
    }
}
