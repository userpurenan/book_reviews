<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

use App\Http\Requests\SignUpRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\Token;
use Laravel\Passport\Client;

class UserController extends Controller
{
    public function signUp(SignUpRequest $request)
    {
        DB::transaction(function () use ($request, &$user){
            $user = User::create([
                "name" => $request->input('name'),
                "email" => $request->input('email'),
                "password" => Hash::make($request->input('password')),
            ]);
        });

        $passport_client = Client::where('name', 'Laravel Password Grant Client')->first();
        $data = [
            'grant_type' => 'password',
            'client_id' => $passport_client->id,
            'client_secret' => $passport_client->secret,
            'username' => $request->input('email'),
            'password' => $request->input('password'),
            'scope' => '',
        ];

        $request = Request::create('/oauth/token', 'POST', $data);
        $response = Route::prepareResponse($request, app()->handle($request));

        $content = $response->getContent();

        $token = json_decode($content, true);

        return response()->json([ 'name' => $user->name, 'token' => $token['access_token'] ],200, [], JSON_UNESCAPED_UNICODE);
    }

    public function login(LoginRequest $request)
    {
        $passport_client = Client::where('name', 'Laravel Password Grant Client')->first();
        $data = [
            'grant_type' => 'password',
            'client_id' => $passport_client->id,
            'client_secret' => $passport_client->secret,
            'username' => $request->input('email'),
            'password' => $request->input('password'),
            'scope' => '',
        ];

        $request = Request::create('/oauth/token', 'POST', $data);
        $response = Route::prepareResponse($request, app()->handle($request));
        $content = $response->getContent();

        $token = json_decode($content, true);

        return response()->json(['access_token' => $token['access_token']]);
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
