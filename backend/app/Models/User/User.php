<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Book\Book;
use App\Models\Book\BookComment;
use App\Models\Book\Reply;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

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
        return $this->hasMany(BookComment::class);
    }

    public function reply()
    {
        return $this->hasMany(Reply::class);
    }
}
