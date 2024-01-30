<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book;
use App\Models\User;

class BookComment extends Model
{
    use HasFactory;

    protected $table = 'book_review_comments';

    protected $fillable = [
        'user_id',
        'book_id',
        'comment',
        'comment_likes',
        'is_reviewer_comment'
    ];

    public function scopeGetBookComment($query, int $book_id, int $number)
    {
        return $query->with('user')->where('book_id', $book_id)->offset($number)->limit(10);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

}
