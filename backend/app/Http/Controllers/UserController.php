<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\SignUpRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;

class UserController extends Controller
{
    public function signUp(SignUpRequest $request)
    {
        if(User::where('email', $request->input('email'))->first()) {
            throw new BadRequestHttpException('そのメールアドレスは既に登録されています');
        }

        DB::transaction(function () use ($request, &$token, &$user) {
            $user = User::create([
                    "name" => $request->input('name'),
                    "email" => $request->input('email'),
                    "password" => Hash::make($request->input('password')),
                ]);

            $token = $user->createToken('Token')->accessToken;
        });

        return response()->json([ 'name' => $user->name ], 200, ['authorization' => $token, 'Access-Control-Expose-Headers' => 'authorization']);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if (is_null($user) || Hash::check($request->input('password'), $user->password) === false) {
            throw new BadRequestHttpException('メールアドレス又はパスワードが間違っています');
        }

        $token = $user->createToken('Token')->accessToken;

        return response()->json([ 'name' => $user->name ], 200, ['authorization' => $token, 'Access-Control-Expose-Headers' => 'authorization']);
    }
}
