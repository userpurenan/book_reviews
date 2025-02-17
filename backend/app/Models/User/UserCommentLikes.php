<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book\BookComment;

class UserCommentLikes extends Model
{
    use HasFactory;

    protected $table = 'user_likes_comment';

    protected $fillable = [
        'user_id',
        'comment_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(BookComment::class);
    }

}
