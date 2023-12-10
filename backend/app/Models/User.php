<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Log;
use App\Models\Token;
use App\Models\Book;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use PhpParser\Node\Expr\Cast\String_;

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
        'imagePath',
        'token',
    ];

    protected $guarded = ['created_at', 'updated_at'];

    public function log(){
        return $this->hasmany(Log::class);
    }

    public function token(){
        return $this->hasmany(Token::class);
    }

    public function book(){
        return $this->hasmany(Book::class);
    }
}
