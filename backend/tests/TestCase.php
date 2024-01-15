<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected string $password;

    protected string $email;

    protected bool $is_passport_install = false;

    public function setUp(): void
    {
        parent::setUp();
        if($this->is_passport_install === false) {
            Artisan::call('passport:install --env=testing');
        }
        $this->is_passport_install = true;
    }

    /**
     * ユーザーを作成するメソッド。
     */
    public function createUser()
    {
        $this->email = fake()->safeEmail();
        $this->password = Str::random(10);

        $user = User::create([
                          'name' => fake()->name(),
                          'email' => $this->email,
                          'password' => Hash::make($this->password)
                      ]);

        return $user;
    }

    public function createToken(string $user_email, string $user_password)
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
