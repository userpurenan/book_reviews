<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Http\Requests\SignUpRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Laravel\Passport\Client;

class UserController extends Controller
{
    /**
     * パスワードグラントを使いトークンを取得するメソッド
     */
    public function passwordGrant($email, $password)
    {
        $passport_client = Client::where('name', 'Laravel Password Grant Client')->first();
        $data = [
            'grant_type' => 'password',
            'client_id' => $passport_client->id,
            'client_secret' => $passport_client->secret,
            'username' => $email,
            'password' => $password,
            'scope' => '',
        ];

        $request = Request::create('/oauth/token', 'POST', $data);
        $response = Route::prepareResponse($request, app()->handle($request));
        $content = $response->getContent();

        $token = json_decode($content, true);

        return $token;
    }

    public function signUp(SignUpRequest $request)
    {
        DB::transaction(function () use ($request, &$user, &$token) {
            $user = User::create([
                "name" => $request->input('name'),
                "email" => $request->input('email'),
                "password" => Hash::make($request->input('password')),
            ]);

            $token = $this->passwordGrant($user->email, $request->input('password'));
        });

        return response()->json([ 'name' => $user->name, 'token' => $token['access_token'] ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function login(LoginRequest $request)
    {
        $token = $this->passwordGrant($request->input('email'), $request->input('password'));

        //パスワードグラントの返り値のjsonに「error」キーがあったらエラーメッセージを返す
        if(array_key_exists('error', $token)) {
            throw new BadRequestHttpException('メールアドレス又はパスワードが間違っています');
        }

        return response()->json(['access_token' => $token['access_token']]);
    }

    public function imageUploads(Request $request)
    {
        $file_path = $request->file('icon')->store('public/img');
        $image_path = str_replace('public', 'storage', $file_path);
        $image_url = asset($image_path);
        $user = User::find(Auth::id());

        if(is_null($user)) {
            throw new NotFoundHttpException('ユーザー情報が見つかりませんでした');
        }

        $user->update(['image_url' => $image_url]);

        return response()->json(['image_url' => $image_url]);
    }

    public function getUser()
    {
        $user = Auth::user();

        if(is_null($user)) {
            throw new NotFoundHttpException('ユーザー情報が見つかりませんでした');
        }

        return response()->json([
                    'name' => $user->name,
                    'image_url' => $user->image_url,
                ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function editUser(Request $request)
    {
        $user = User::find(Auth::id());

        if(is_null($user)) {
            throw new NotFoundHttpException('ユーザー情報が見つかりませんでした');
        }

        $user->update(['name' => $request->input('name')]);
    }
}
