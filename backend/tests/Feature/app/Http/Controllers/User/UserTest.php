<?php

namespace Tests\Feature\App\Http\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_会員登録できるか？(): void
    {
        $response = $this->post('/api/signup', [
                                'name' => fake()->name(),
                                'email' => fake()->safeEmail(),
                                'password' => Str::random(10)
                            ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('users', 1);
    }

    /**
     * @dataProvider signUpValidFailsProvider
     */
    public function test_会員登録時のバリデーションチェック($name, $email, $password)
    {
        $invalid = $this->post('/api/signup', [
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        $invalid->assertStatus(422);
    }

    public static function signUpValidFailsProvider()
    {
        return[
            '無効なメールアドレス' => [ fake()->name(), 'sample', Str::random(10) ],
            '無効なパスワード（4文字以下）' => [ fake()->name(), fake()->safeEmail(), Str::random(rand(1, 4)) ],
            '無効なパスワード（16文字以下）' => [ fake()->name(), fake()->safeEmail(), Str::random(rand(16, 17)) ],
            '名前が入力されていない状態' => [ '', fake()->safeEmail(), Str::random(10) ],
            'メールアドレスが入力されていない状態' => [ fake()->name(), '', Str::random(10) ],
            'パスワードが入力されていない状態' => [ fake()->name(), fake()->safeEmail(), '' ]
        ];
    }

    public function test_ログインできるか？()
    {
        $user = $this->createUser();
        $response = $this->post('/api/login', [
                            'email' => $user->email,
                            'password' => $this->password
                        ]);

        $response->assertStatus(200);
        $response->assertJsonMissing(['access_token' => '']);
    }

    /**
     * @dataProvider loginValidFailsProvider
     */
    public function test_ログインの際のバリデーションチェック($email, $password)
    {
        $invalid = $this->post('/api/login', [
            'email' => $email,
            'password' => $password
        ]);

        $invalid->assertStatus(422);
    }

    public static function loginValidFailsProvider()
    {
        return[
            '無効なメールアドレス' => [ 'sample', Str::random(10) ],
            '無効なパスワード（4文字以下）' => [ fake()->safeEmail(), Str::random(rand(1, 4)) ],
            '無効なパスワード（16文字以下）' => [ fake()->safeEmail(), Str::random(rand(16, 17)) ],
            'メールアドレスが入力されていない状態' => [ '', Str::random(10) ],
            'パスワードが入力されていない状態' => [ fake()->safeEmail(), '' ]
        ];
    }
}
