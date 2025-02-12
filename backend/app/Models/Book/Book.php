<?php

declare(strict_types=1);

namespace App\Models\Book;

use App\Models\User\User;
use App\Models\Book\BookComment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function bookComment()
    {
        return $this->hasMany(BookComment::class);
    }

}
