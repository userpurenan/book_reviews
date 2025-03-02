<?php

namespace App\Models\UserDomain;

use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserReviewLikes extends Model
{
    use HasFactory;

    protected $table = 'user_likes_review';

    protected $fillable = [
        'user_id',
        'book_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
