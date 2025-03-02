<?php

namespace App\Models\UserDomain;

use App\Models\BookDomain\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        return $this->belongsTo(Comment::class);
    }

}
