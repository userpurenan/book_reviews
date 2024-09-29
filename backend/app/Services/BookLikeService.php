<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\DB;

class BookLikeService
{
    private UpdateLikeStatusService $update_like_status;

    public function __construct(UpdateLikeStatusService $update_like_status)
    {
        $this->update_like_status = $update_like_status;
    }

    public function updateReviewLikes(int $book_id, int $likes_count_change)
    {
        $book = Book::findOrFail($book_id);

        $retryTimes = 3;
        DB::transaction(function () use ($book, $likes_count_change, &$is_review_likes) {
            $likes_count_change = $likes_count_change; //「1」か「-1」が渡される
            $book_likes_count = $book->likes + $likes_count_change;

            $book->update(['likes' => $book_likes_count ]);

            // 可読性向上の目的で、いいねの状態を管理するテーブル操作はサービスクラスに切り出した
            $is_review_likes = $this->update_like_status->updateBookReviewLikeStatus($book, $likes_count_change);
        }, $retryTimes);

        return [
            'likes' => $book->likes,
            'is_review_likes' => $is_review_likes,
        ];
    }
}
