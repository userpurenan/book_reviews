<?php

namespace Tests\Feature\App\Http\Controllers\User;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\UserDomain\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthUserControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $password;

    private string $email;

    private $user;

    private $token;

    public function setUp(): void
    {
        parent::setUp();
        $this->email = fake()->safeEmail();
        $this->password = Str::random(10);

        $this->user = User::create([
                          'name' => fake()->name(),
                          'email' => $this->email,
                          'password' => Hash::make($this->password)
                      ]);

        $this->token = $this->user->createToken('Token')->accessToken;
    }

    public function test_ユーザー情報が取得できる(): void
    {
        $response = $this->get('/api/user', [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(200);
        $response->assertJson(['name' => $this->user->name,
                               'image_url' => $this->user->imege_url
                              ]);
    }

    public function test_アイコン画像のURLを保存できる()
    {
        $image_file = UploadedFile::fake()->image('icon.jpg');

        $response = $this->post('/api/upload', [
            'icon' => $image_file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(200);
        $response->assertJson(['image_url' => $response['image_url'] ]);
        $this->assertDatabaseMissing('users', [
            'image_url' => null,
        ]);
    }

    public function test_ユーザーの名前の変更ができる()
    {
        $update_name = fake()->name();

        //ユーザー名の変更
        $this->patch('/api/user', [
            'name' => $update_name
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => $this->user->name,
        ]);
        $this->assertDatabaseHas('users', [
            'name' => $update_name
        ]);
    }

    public function test_ユーザーのアイコンの変更ができる()
    {
        $file1 = UploadedFile::fake()->image('icon.jpg');
        $file2 = UploadedFile::fake()->image('icon2.jpg');

        //画像のURL保存
        $create_user_icon_response = $this->post('/api/upload', [
            'icon' => $file1,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        //ユーザーの画像の変更
        $update_user_icon_response = $this->post('api/upload', [
            'icon' => $file2,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $this->assertDatabaseMissing('users', [
            'image_url' => $create_user_icon_response['image_url'],
        ]);
        $this->assertDatabaseHas('users', [
            'image_url' => $update_user_icon_response['image_url'],
        ]);
    }
}
