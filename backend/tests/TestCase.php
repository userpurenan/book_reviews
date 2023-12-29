<?php

namespace Tests;

use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;


abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $password;

    protected $email;

    /**
     * ユーザーを作成するメソッド。
     */
    public function createUser()
    {
        $faker = Factory::create('ja_JP');
        $this->email = $faker->safeEmail;
        $this->password = Str::random(10);

        $user = User::create([
                          'name' => '岩崎太郎',
                          'email' => $this->email,
                          'password' => Hash::make($this->password)
                      ]);

        return $user;
    }

    public function createToken($user_email, $user_password)
    {
        $passport_client = Client::where('name', 'Laravel Password Grant Client')->first();
        $data = [
            'grant_type' => 'password',
            'client_id' => $passport_client->id,
            'client_secret' => $passport_client->secret,
            'username' => $user_email,
            'password' => $user_password,
            'scope' => '',
        ];

        $request = Request::create('/oauth/token', 'POST', $data);
        $response = Route::prepareResponse($request, app()->handle($request));

        $content = $response->getContent();

        $token = json_decode($content, true);

        return $token['access_token'];
    }
}
