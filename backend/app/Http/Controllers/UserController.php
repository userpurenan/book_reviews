<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Http\Requests\SignUpRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\Token;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UserController extends Controller
{
    public function signUp(SignUpRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                        "name" => $request->input('name'),
                        "email" => $request->input('email'),
                        "password" => Hash::make($request->input('password')),
                    ]);

            $credentials = $request->only(['email', 'password']);
            if(! $token = Auth::attempt($credentials)) throw new \Exception('トークンの取得に失敗しました。') ;

            Token::create(['user_id' => $user->id,
                           'token' => $token]);

            DB::commit();

            return response()->json([ 'name' => $user->name, 'token' => $token ],200, [], JSON_UNESCAPED_UNICODE)->header('Authorization', 'Bearer '.$token);
        } catch(\Exception $e) {
            Log::critical($e->getTraceAsString());
            DB::rollBack();
            return response()->json(['errormessage' => $e->getMessage() ]);
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if(! $token = Auth::attempt($credentials)) {
            throw new BadRequestHttpException('パスワード又はメールアドレスが間違っています');
        }

        $user = User::where('email', $request->input('email'))->first();

        Token::create(['user_id' => $user->id,
                       'token' => $token]);

        return response()->json([ 'message' => 'auth success', 'token' => $token ], 200)->header('Authorization', 'Bearer '.$token);
    }

    public function imageUploads(Request $request)
    {
        $imagePath = $request->file('icon')->store('public/img');
        $token = $request->bearerToken();
        $user = Token::where('token', $token)->first()->user;

        if(is_null($user)){
            throw new NotFoundHttpException('ユーザー情報が見つかりませんでした');
        }

        $user->update(['imagePath' => '../../../backend/storage/app/' . $imagePath]);

        return response()->json(['imagePath' => "backend/storage/app/" . $imagePath]);
    }

    public function getUser()
    {
        $user = Auth::user();

        if(is_null($user)) {
            throw new NotFoundHttpException('ユーザー情報が見つかりませんでした');
        }

        return response()->json([
                    'name' => $user->name,
                    'imagePath' => $user->imagePath,
                ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function editUser(Request $request)
    {
        $user = Token::where('token', $request->bearerToken())->first()->user;
              
        $user->update(['name' => $request->input('name')]);
    }
}
