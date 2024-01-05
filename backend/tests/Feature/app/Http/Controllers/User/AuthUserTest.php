<?php

namespace Tests\Feature\App\Http\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AuthUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_ユーザー情報が取得できるか？(): void
    {
        $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $response = $this->get('/api/users', [
            'Authorization' => "Bearer ".$token,
        ]);

        $response->assertStatus(200);
    }

    public function test_アイコン画像のURLを保存できるか？()
    {
        $image_file = UploadedFile::fake()->image('icon.jpg');
        $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $response = $this->post('/api/uploads', [
            'icon' => $image_file,
        ],[
            'Authorization' => "Bearer ".$token,
        ]);

        $response->assertStatus(200);
    }

    public function test_ユーザーの名前の変更ができるか？()
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);
        
        //ユーザー名の変更
        $this->patch('/api/users', [
            'name' => 'なかじま'
        ],[
            'Authorization' => "Bearer ".$token,
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => $user->name,
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'なかじま'
        ]);
    }

    public function test_ユーザーのアイコンの変更ができるか？()
    {
        $file1 = UploadedFile::fake()->image('icon.jpg');
        $file2 = UploadedFile::fake()->image('icon2.jpg');

        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

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
