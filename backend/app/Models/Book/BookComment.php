<?php

declare(strict_types=1);

namespace App\Models\Book;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book\Book;
use App\Models\User\User;

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

    public function scopeGetBookComment($query, int $book_id)
    {
        return $query->with('user')->where('book_id', $book_id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function commentReply()
    {
        return $this->hasMany(CommentReply::class);
    }

}
