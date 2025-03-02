<?php

declare(strict_types=1);

namespace App\Models\BookDomain;

use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
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
        return $this->hasMany(Reply::class);
    }

}
