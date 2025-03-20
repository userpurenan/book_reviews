<?php

declare(strict_types=1);

namespace App\Models\BookDomain;

use App\Models\UserDomain\User;
use App\Models\BookDomain\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $table = "books";

    protected $fillable = [
        'title',
        'user_id',
        'url',
        'detail',
        'review',
        'reviewer',
        'likes',
        'spoiler',
    ];

    protected $guarded = ['created_at', 'updated_at'];

    public function scopeBookSearch($query, string $keyword = '')
    {
        return $query->select('id', 'title', 'likes')->where("title", "LIKE", "%$keyword%");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'review_likes');
    }

    public function comment()
    {
        return $this->hasMany(Comment::class);
    }

}
