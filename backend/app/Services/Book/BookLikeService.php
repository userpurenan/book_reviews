<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Models\Book;
use Illuminate\Support\Facades\DB;
use App\Services\Book\UpdateLikeStatusService;

class BookLikeService
{
    private UpdateLikeStatusService $update_like_status;

    public function __construct(UpdateLikeStatusService $update_like_status)
    {
        $this->update_like_status = $update_like_status;
    }

    public function updateLikes(int $book_id, int $likes_count_change)
    {
        $book = Book::findOrFail($book_id);
        $likes_count_change = $likes_count_change; //「1」か「-1」が渡される
        $new_likes_count = $book->likes + $likes_count_change;

        if($new_likes_count < 0) {
            return [ 'error' => 'いいねは0未満にはできません' ];
        }

        $retryTimes = 3;
        DB::transaction(function () use ($book, $new_likes_count, $likes_count_change) {
            $book->update(['likes' => $new_likes_count ]);

            // 可読性向上の目的で、いいねの状態を管理するテーブル操作はサービスクラスに切り出した
            $this->update_like_status->updateBookReviewLikeStatus($book, $likes_count_change);
        }, $retryTimes);

        return [ 'review_likes' => $book->likes ];
    }
}
