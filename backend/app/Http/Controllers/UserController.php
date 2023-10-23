<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function signUp(Request $request)
    {
        DB::beginTransaction();

        try {
            User::insert([
                "name" => $request->input('name'),
                "email" => $request->input('email'),
                "password" => Hash::make($request->input('password')),
            ]);

            $credentials = $request->only(['email', 'password']);
            if(! $token = auth()->attempt($credentials)) throw new \Exception('トークンの取得に失敗しました。') ;

            User::where('email', $request->input('email'))->update(['token' => 'Bearer ' . $token]);

            DB::commit();

            return response()->json([
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth("api")->factory()->getTTL() * 60
                ]);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['errormessage' => $e->getMessage() ]);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (! $token = auth()->attempt($credentials)) throw new \Exception('パスワード又はメールアドレスが間違っています');

            User::where('email', $request->input('email'))->update(['token' => 'Bearer ' . $token]);

            return response()->json([
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth("api")->factory()->getTTL() * 60
            ]);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function imgUpdate(Request $request)
    {
        $imgPath = $request->file('icon')->store('public/img');

        User::where('token', $request->headers->get('Authorization'))
                ->update(['imgPath' => 'backend/storage/app/' . $imgPath]);

        return response()->json(['imgPath' => "backend/storage/app/" . $imgPath]);
    }

    public function getUser(Request $request)
    {
        $user_info = User::where('token', $request->headers->get('Authorization'))->first();

        return response()->json([
                    'name' => $user_info->name,
                    'imgPath' => $user_info->imgPath,
                ],200, [], JSON_UNESCAPED_UNICODE);
    }

    public function editUser(Request $request)
    {
        User::where('token', $request->headers->get('Authorization'))
              ->update(['name' => $request->input('name')]);
    }

}
