<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use backend\App\Models\User;

class Log extends Model
{
    use HasFactory;

    protected $table = "logs";

    protected $fillable = [
        'user_id',
        'access_log'
    ];

    protected $guarded = ['created_at', 'updated_at'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
