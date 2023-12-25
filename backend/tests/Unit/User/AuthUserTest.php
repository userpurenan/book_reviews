<?php

namespace Tests\Unit\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;
use Tests\TestCase;

class AuthUserTest extends TestCase
{
    use RefreshDatabase;

    private $password;

    /**
     * ユーザーを作成するメソッド。
     */
    public function create_user()
    {
        $faker = Factory::create('ja_JP');
        $this->password = Str::random(10);

        $user = User::create([
                        'name' => $faker->name,
                        'email' => $faker->safeEmail,
                        'password' => Hash::make($this->password)
                    ]);

        $passport_client = Client::where('name', 'Laravel Password Grant Client')->first();
        $data = [
            'grant_type' => 'password',
            'client_id' => $passport_client->id,
            'client_secret' => $passport_client->secret,
            'username' => $user->email,
            'password' => $this->password,
            'scope' => '',
        ];

        $request = Request::create('/oauth/token', 'POST', $data);
        $response = Route::prepareResponse($request, app()->handle($request));

        $content = $response->getContent();

        $token = json_decode($content, true);

        return $token;
    }

    /**
     * A basic feature test example.
     */
    public function test_ユーザー情報が取得できるか？(): void
    {
        $signUp_response = $this->create_user();
        $token = $signUp_response['access_token'];

        $response = $this->get('/api/users', [
            'Authorization' => "Bearer ".$token,
        ]);

        $response->assertStatus(200);
    }
}
