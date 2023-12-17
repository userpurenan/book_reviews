<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book;
use App\Models\User;

class BookComment extends Model
{
    use HasFactory;

    protected $table = 'book_review_comment';

    protected $fillable = [
        'user_id',
        'book_id',
        'comment',
        'comment_likes'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function book(){
        return $this->belongsTo(Book::class, 'book_id');
    }

}
