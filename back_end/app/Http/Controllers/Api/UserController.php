<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function signIn(){
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $access_token = str_random(220);

        User::insert([
            "name" => $name,
            "email" => $email,
            "password" => $password
        ]);
        
        return response()->header('Access-Control-Allow-Origin', 'http://localhost:3000')->json([ 'token' => $access_token ], 200);
    }
}
