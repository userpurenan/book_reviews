<?php

namespace App\Models\UserDomain;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\BookDomain\Book;
use App\Models\BookDomain\Reply;
use App\Models\BookDomain\Comment;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

    protected $table = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [ //データベースを操作するときに使うカラム名（新しいレコードを作成するとき）
        'name',
        'email',
        'password',
        'image_url',
    ];

    protected $guarded = ['created_at', 'updated_at'];

    public function book()
    {
        return $this->hasmany(Book::class);
    }

    public function books_comment()
    {
        return $this->hasMany(Comment::class);
    }

    public function reply()
    {
        return $this->hasMany(Reply::class);
    }
}
