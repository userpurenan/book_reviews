<?php

declare(strict_types=1);

namespace App\Models\Book;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentReply extends Model
{
    use HasFactory;

    protected $table = 'reply';

    protected $fillable = [
        'comment_id',
        'user_id',
        'reply',
        'reply_likes',
        'is_reviewer_reply'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(BookComment::class, 'comment_id');
    }

}
