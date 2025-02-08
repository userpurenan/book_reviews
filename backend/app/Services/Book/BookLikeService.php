<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Models\Book\Book;
use Illuminate\Support\Facades\DB;
use App\Services\Book\UpdateLikeStatusService;

class BookLikeService
{
    private UpdateLikeStatusService $update_like_status;

    public function __construct(UpdateLikeStatusService $update_like_status)
    {
        $this->update_like_status = $update_like_status;
    }

    public function updateLikes(int $book_id, int $likes)
    {
        $book = Book::findOrFail($book_id);
        $new_likes_count = $book->likes + $likes;

        if($new_likes_count < 0) {
            // controllerで、「返り値の配列のキーにerrorがあったら500エラーを返す」という処理を書いているので、
            // ここでは、errorをキーに取った配列（＋エラーメッセージ）を返す
            return [ 'error' => 'いいねは0未満にはできません' ];
        }

        $retryTimes = 3;
        DB::transaction(function () use ($book, $new_likes_count, $likes) {
            $book->update(['likes' => $new_likes_count ]);

            // 可読性向上の目的で、いいねの状態を管理するテーブル操作はサービスクラスに切り出した
            $this->update_like_status->updateBookReviewLikeStatus($book, $likes);
        }, $retryTimes);

        return [ 'review_likes' => $book->likes ];
    }
}
