<?php

declare(strict_types=1);

namespace App\Http\Controllers\UserDomain;

use App\Models\UserDomain\User;
use App\Http\Requests\LoginRequest;

use App\Http\Controllers\Controller;

use App\Http\Requests\SignUpRequest;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserController extends Controller
{
    public function signUp(SignUpRequest $request)
    {
        if(User::where('email', $request->input('email'))->first()) {
            throw new BadRequestHttpException('そのメールアドレスは既に登録されています');
        }

        $user = User::create([
            "name" => $request->input('name'),
            "email" => $request->input('email'),
            "password" => Hash::make($request->input('password')),
        ]);

        $token = $user->createToken('Token')->accessToken;

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
