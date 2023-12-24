<?php

namespace Tests\Feature\User;

use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $password;

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

        return $user;
    }

    public function test_グラントキーがデータベースに保存されるか？(): void
    {
        $cmd = 'php artisan passport:install --env=testing';
        exec($cmd);

        $this->assertDatabaseHas('oauth_clients', [
            'name' => 'Laravel Password Grant Client',
        ]);
    }

    public function test_会員登録できるか？(): void
    {
        $this->create_user();
        $this->assertDatabaseCount('users', 1);

        $faker = Factory::create('ja_JP');
        $response = $this->post('/api/signup',[
                                'name' => $faker->name,
                                'email' => $faker->safeEmail,
                                'password' => 'iwasaki'
                            ]);

        $response->assertStatus(200);
    }

    public function test_ログインできるか？()
    {
        $user = $this->create_user();
        $response = $this->post('/api/login', [
                            'email' => $user->email,
                            'password' => $this->password
                        ]);
        
        $response->assertStatus(200);
    }
}
