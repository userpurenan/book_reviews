<?php

declare(strict_types=1);

namespace Tests\Feature\App\Http\Controllers\UserDomain;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\UserDomain\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_アカウント作成することができる(): void
    {
        $email = fake()->safeEmail();
        $response = $this->post('/api/signup', [
                                'name' => fake()->name(),
                                'email' => $email,
                                'password' => Str::random(10)
                            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => $email]); //emailカラムはユニークなので$emailがデータベースに確認出来たらアカウント作成できたといえる
    }

    /**
     * @dataProvider failSignUpValidProvider
     */
    public function test_無効な入力を会員登録時にバリデーションではじく(string $name, string $email, string $password)
    {
        $invalid = $this->post('/api/signup', [
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        $invalid->assertStatus(422);
    }

    /**
     * 全ての入力欄に「required」バリデーションを実装しています。
     * パスワードは5文字以上15文字以下の長さに収まらないとバリデーションではじく設定にしています。
     * メールアドレスは「hoge@hoge.com」このような形式以外ははじく設定にしています。
     * 名前は「required」のみです。
     */
    public static function failSignUpValidProvider()
    {
        //以下の返り値は全てバリデーションではじいてくれることを正常として想定しています。
        return[
            '無効なメールアドレス形式（「@」無し）' => [ fake()->name(), 'sample', Str::random(10) ],
            '無効なメールアドレス形式（ひらがな）' => [ fake()->name(), 'さんぷる@さんぷる.com', Str::random(10) ],
            '無効なメールアドレス形式（カタカナ）' => [ fake()->name(), 'サンプル@サンプル.com', Str::random(10) ],
            '無効なメールアドレス形式（漢字）' => [ fake()->name(), '例形式@例形式.com', Str::random(10) ],
            '無効なパスワード（4文字以下）' => [ fake()->name(), fake()->safeEmail(), Str::random(rand(1, 4)) ],
            '無効なパスワード（16文字以上）' => [ fake()->name(), fake()->safeEmail(), Str::random(rand(16, 17)) ],
            '名前が入力されていない状態' => [ '', fake()->safeEmail(), Str::random(10) ],
            'メールアドレスが入力されていない状態' => [ fake()->name(), '', Str::random(10) ],
            'パスワードが入力されていない状態' => [ fake()->name(), fake()->safeEmail(), '' ],
            '名前とメールアドレスが入力されていない状態' => [ '', '', Str::random(10) ],
            '名前とパスワードが入力されていない状態' => [ '', fake()->safeEmail(), '' ],
            'メールアドレスとパスワードが入力されていない状態' => [ fake()->name(), '', '' ]
        ];
    }

    public function test_ログインすることができる()
    {
        $email = fake()->safeEmail();
        $password = Str::random(10);

        $user = User::create([
                          'name' => fake()->name(),
                          'email' => $email,
                          'password' => Hash::make($password)
                      ]);

        $response = $this->post('/api/login', [
                            'email' => $user->email,
                            'password' => $password
                        ]);

        $response->assertStatus(200);
        $response->assertJsonMissing(['access_token' => '']);
    }

    /**
     * @dataProvider failLoginValidProvider
     */
    public function test_無効な入力をログインの際にバリデーションではじく(string $email, string $password)
    {
        $invalid = $this->post('/api/login', [
            'email' => $email,
            'password' => $password
        ]);

        $invalid->assertStatus(422);
    }

    /**
     * 全ての入力欄に「required」バリデーションを実装しています。
     * パスワードは5文字以上15文字以下の長さに収まらないとバリデーションではじく設定にしています。
     * メールアドレスは「hoge@hoge.com」このような形式以外ははじく設定にしています。
     */
    public static function failLoginValidProvider()
    {
        //以下の返り値は全てバリデーションではじいてくれることを正常として想定しています。
        return[
            '無効なメールアドレス形式（「@」無し）' => [ 'sample', Str::random(10) ],
            '無効なメールアドレス形式（ひらがな）' => [ 'さんぷる@さんぷる.com', Str::random(10) ],
            '無効なメールアドレス形式（カタカナ）' => [ 'サンプル@サンプル.com', Str::random(10) ],
            '無効なメールアドレス形式（漢字）' => [ '例形式@例形式.com', Str::random(10) ],
            '無効なパスワード（4文字以下）' => [ fake()->safeEmail(), Str::random(rand(1, 4)) ],
            '無効なパスワード（16文字以下）' => [ fake()->safeEmail(), Str::random(rand(16, 17)) ],
            'メールアドレスが入力されていない状態' => [ '', Str::random(10) ],
            'パスワードが入力されていない状態' => [ fake()->safeEmail(), '' ],
            'メールアドレスとパスワードが入力されていない状態' => [ fake()->name(), '', '' ]
        ];
    }
}
