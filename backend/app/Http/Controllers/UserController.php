<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use backend\App\Models\User;

class UserController extends Controller
{
    public function signIn(Request $request){
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $access_token = Str::random(220);

        DB::table('users')->insert([
            "name" => $name,
            "email" => $email,
            "password" => Hash::make($password),
            "token" => $access_token,
        ]);
        
        return response()->json([ 'token' => $access_token ], 200);
    }
}
