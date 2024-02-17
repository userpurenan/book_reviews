<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\User;
use App\Models\BookComment;
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
        'spoiler',
    ];

    protected $guarded = ['created_at', 'updated_at'];

    public function scopeBookSearch($query, string $keyword, int $number)
    {
        return $query->select('id', 'title')->where("title", "LIKE", "%$keyword%")->offset($number)->limit(10);
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
