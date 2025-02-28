<?php

namespace App\Models\User;

use App\Models\Book\Reply;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReplyLikes extends Model
{
    use HasFactory;

    protected $table = 'reply_likes';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'reply_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reply()
    {
        return $this->belongsTo(Reply::class);
    }
}
