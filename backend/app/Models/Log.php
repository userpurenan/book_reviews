<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = "logs";

    protected $fillable = [
        'user_id',
        'access_log'
    ];

    protected $guarded = ['created_at', 'updated_at'];

}
