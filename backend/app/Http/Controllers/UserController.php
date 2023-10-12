<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function signUp(Request $request){
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $access_token = Str::random(220);

        User::insert([
            "name" => $name,
            "email" => $email,
            "password" => Hash::make($password),
            "token" => $access_token,
        ]);
        
        return response()->json([ 'token' => $access_token ], 200);
    }

    public function login(Request $request){
        try{
            $access_token = Str::random(220);
            $credentials = $request->only('email', 'password');
            $guard = $request->guard;

            if (!Auth::guard($guard)->attempt($credentials)) throw new \Exception('パスワード又はメールアドレスが間違っています');

            return response()->json([ 'token' => $access_token ], 200);
        }catch(\Exception $e){
            return response()->json(['error' => $e], 500);        
        }
    }

}
