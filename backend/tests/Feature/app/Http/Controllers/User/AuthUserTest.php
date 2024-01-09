<?php

namespace Tests\Feature\App\Http\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AuthUserTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $cmd = 'php artisan passport:install --env=testing';
        exec($cmd);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $cmd = 'php artisan migrate:refresh --env=testing';
        exec($cmd);
    }

    public function test_ユーザー情報が取得できる(): void
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $response = $this->get('/api/user', [
            'Authorization' => "Bearer " . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['name' => $user->name,
                               'image_url' => $user->imege_url
                              ]);
    }

    public function test_アイコン画像のURLを保存できる()
    {
        $image_file = UploadedFile::fake()->image('icon.jpg');
        $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $response = $this->post('/api/upload', [
            'icon' => $image_file,
        ], [
            'Authorization' => "Bearer " . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['image_url' => $response['image_url'] ]);
        $this->assertDatabaseMissing('users', [
            'image_url' => null
        ]);
    }

    public function test_ユーザーの名前の変更ができる()
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        //ユーザー名の変更
        $this->patch('/api/user', [
            'name' => 'なかじま'
        ], [
            'Authorization' => "Bearer " . $token,
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => $user->name,
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'なかじま'
        ]);
    }

    public function test_ユーザーのアイコンの変更ができる()
    {
        $file1 = UploadedFile::fake()->image('icon.jpg');
        $file2 = UploadedFile::fake()->image('icon2.jpg');

        $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        //画像のURL保存
        $create_user_icon_response = $this->post('/api/upload', [
            'icon' => $file1,
        ], [
            'Authorization' => "Bearer " . $token,
        ]);

        //ユーザーの画像の変更
        $edit_user_icon_response = $this->post('api/upload', [
            'icon' => $file2,
        ], [
            'Authorization' => "Bearer " . $token,
        ]);

        $this->assertDatabaseMissing('users', [
            'image_url' => $create_user_icon_response['image_url'],
        ]);
        $this->assertDatabaseHas('users', [
            'image_url' => $edit_user_icon_response['image_url'],
        ]);
    }
}
