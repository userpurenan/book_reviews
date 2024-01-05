<?php

namespace Tests\Feature\App\Http\Controllers\User;

use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_会員登録できるか？(): void
    {
        $this->assertDatabaseCount('users', 0);
        $faker = Factory::create('ja_JP');
        $response = $this->post('/api/signup',[
                                'name' => $faker->name,
                                'email' => $faker->safeEmail,
                                'password' => Str::random(10)
                            ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('users', 1);
    }

    /**
     * @dataProvider validationFailsProvider
     */
    public function test_会員登録時のバリデーションチェック($name, $email, $password)
    {
        $invalid = $this->post('/api/signup',[
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        $invalid->assertStatus(422);
    }

    public static function validationFailsProvider()
    {
        $faker = Factory::create('ja_JP');
        return[
            '無効なメールアドレス' => [ $faker->name, 'sample', Str::random(10) ],
            '無効なパスワード（4文字以下）' => [ $faker->name, $faker->safeEmail, Str::random(rand(1,4)) ],
            '無効なパスワード（16文字以下）' => [ $faker->name, $faker->safeEmail, Str::random(rand(16, 17)) ],
            '名前が入力されていない状態' => [ '', $faker->safeEmail, Str::random(10) ],
            'メールアドレスが入力されていない状態' => [ $faker->name, '', Str::random(10) ],
            'パスワードが入力されていない状態' => [ $faker->name, $faker->safeEmail, '' ]
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
     * @dataProvider validationFailsProvider
     */
    public function test_ログインの際にバリデーションルールが適応されているか？($email, $password)
    {
        $invalid = $this->post('/api/login',[
            'email' => $email,
            'password' => $password
        ]);

        $invalid->assertStatus(422);
    }
}
