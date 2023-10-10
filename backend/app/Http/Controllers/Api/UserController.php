<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use back_end\app\Models\User;

class UserController extends Controller
{
    public function signIn(Request $request){
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $access_token = str_random(220);

        User::insert([
            "name" => $name,
            "email" => $email,
            "password" => $password
        ]);
        
        return response()->json([ 'token' => $access_token ], 200);
    }
}
