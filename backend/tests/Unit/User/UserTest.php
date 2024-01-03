<?php

namespace Tests\Unit\User;

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

    public function test_会員登録できるか？(): void
    {
        $this->create_user();
        $this->assertDatabaseCount('users', 1);

        $faker = Factory::create('ja_JP');
        $response = $this->post('/api/signup',[
                                'name' => $faker->name,
                                'email' => $faker->safeEmail,
                                'password' => $this->password
                            ]);

        $response->assertStatus(200);
    }

    public function test_会員登録の際にバリデーションルールが適応されているか？()
    {
        $faker = Factory::create('ja_JP');

        //無効なメールアドレス形式
        $invalid_email_adress = $this->post('/api/signup',[
                                    'name' => $faker->name,
                                    'email' => 'sample',
                                    'password' => Str::random(10)
                                ]);

        $invalid_email_adress->assertStatus(422);

        //無効なパスワード(4文字以下)
        $invalid_password_length_max4 = $this->post('/api/signup',[
                                            'name' => $faker->name,
                                            'email' => $faker->safeEmail,
                                            'password' => Str::random(rand(1,4))
                                        ]);
        
        //無効なパスワード（16文字以上）
        $invalid_password_length_min16 = $this->post('/api/signup',[
                                            'name' => $faker->name,
                                            'email' => $faker->safeEmail,
                                            'password' => Str::random(rand(16, 17))
                                        ]);
        
        $invalid_password_length_max4->assertStatus(422);
        $invalid_password_length_min16->assertStatus(422);

        //名前が入力されていない状態
        $invalid_name_null = $this->post('/api/signup',[
                                'name' => '',
                                'email' => $faker->safeEmail,
                                'password' => Str::random(10)
                            ]);

        //メールアドレスが入力されていない状態
        $invalid_email_null = $this->post('/api/signup',[
                                'name' => $faker->name,
                                'email' => '',
                                'password' => Str::random(rand(16, 17))
                            ]);

        //パスワードが入力されていない状態
        $invalid_password_null = $this->post('/api/signup',[
                                    'name' => $faker->name,
                                    'email' => $faker->safeEmail,
                                    'password' => ''
                                ]);

        $invalid_name_null->assertStatus(422);
        $invalid_email_null->assertStatus(422);
        $invalid_password_null->assertStatus(422);
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

    public function test_ログインの際にバリデーションルールが適応されているか？()
    {
        $faker = Factory::create('ja_JP');

        //無効なメールアドレス形式
        $invalid_email_adress = $this->post('/api/login',[
                                    'name' => $faker->name,
                                    'email' => 'sample',
                                    'password' => Str::random(10)
                                ]);

        $invalid_email_adress->assertStatus(422);

        //無効なパスワード(4文字以下)
        $invalid_password_length_max4 = $this->post('/api/login',[
                                            'name' => $faker->name,
                                            'email' => $faker->safeEmail,
                                            'password' => Str::random(rand(1,4))
                                        ]);
        
        //無効なパスワード（16文字以上）
        $invalid_password_length_min16 = $this->post('/api/login',[
                                            'name' => $faker->name,
                                            'email' => $faker->safeEmail,
                                            'password' => Str::random(rand(16, 17))
                                        ]);
        
        $invalid_password_length_max4->assertStatus(422);
        $invalid_password_length_min16->assertStatus(422);

        //メールアドレスが入力されていない状態
        $invalid_email_null = $this->post('/api/login',[
                                'name' => $faker->name,
                                'email' => '',
                                'password' => Str::random(rand(16, 17))
                            ]);

        //パスワードが入力されていない状態
        $invalid_password_null = $this->post('/api/login',[
                                    'name' => $faker->name,
                                    'email' => $faker->safeEmail,
                                    'password' => ''
                                ]);

        $invalid_email_null->assertStatus(422);
        $invalid_password_null->assertStatus(422);
    }


}
