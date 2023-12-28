<?php

namespace Tests\Unit\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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
                        'name' => 'いわさき',
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

    public function test_アイコン画像のURLを保存できるか？()
    {
        $image_file = UploadedFile::fake()->image('icon.jpg');
        $signUp_response = $this->create_user();
        $token = $signUp_response['access_token'];

        $response = $this->post('/api/uploads', [
            'icon' => $image_file,
        ],[
            'Authorization' => "Bearer ".$token,
        ]);

        $response->assertStatus(200);
    }

    public function test_ユーザー情報の変更ができるか？()
    {
        $file1 = UploadedFile::fake()->image('icon.jpg');
        $file2 = UploadedFile::fake()->image('icon2.jpg');

        $signUp_response = $this->create_user();
        $token = $signUp_response['access_token'];

        //画像のURL保存
        $create_user_icon = $this->post('/api/uploads', [
            'icon' => $file1,
        ],[
            'Authorization' => "Bearer ".$token,
        ]);
        $user_icon_url = $create_user_icon['imageUrl'];
        $this->assertDatabaseHas('users', [
            'imageUrl' => $user_icon_url,
        ]);
        
        //ユーザー名の変更
        $this->patch('/api/users', [
            'name' => 'なかじま'
        ],[
            'Authorization' => "Bearer ".$token,
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => 'いわさき',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'なかじま'
        ]);

        //ユーザーの画像の変更
        $edit_user_icon = $this->post('api/uploads', [
            'icon' => $file2,
        ],[
            'Authorization' => "Bearer ".$token,
        ]);
        $user_icon_url2 = $edit_user_icon['imageUrl'];

        $this->assertDatabaseMissing('users', [
            'imageUrl' => $user_icon_url,
        ]);
        $this->assertDatabaseHas('users', [
            'imageUrl' => $user_icon_url2,
        ]);

    }
}
