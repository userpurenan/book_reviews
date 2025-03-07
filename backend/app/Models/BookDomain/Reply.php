<?php

declare(strict_types=1);

namespace App\Models\BookDomain;

use App\Models\BookDomain\Comment;
use App\Models\UserDomain\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reply extends Model
{
    use HasFactory;

    protected $table = 'replies';

    protected $fillable = [
        'comment_id',
        'user_id',
        'content',
        'likes',
        'is_reviewer_reply'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

}
