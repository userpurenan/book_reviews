<?php

namespace Tests\Unit\Book;

use App\Models\Book;
use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private $password;

    private $email;

    /**
     * ユーザーを作成するメソッド。
     */
    public function create_user()
    {
        $faker = Factory::create('ja_JP');
        $this->password = Str::random(10);
        $this->email = $faker->safeEmail;

        $user = User::create([
                          'name' => '岩崎太郎',
                          'email' => $this->email,
                          'password' => Hash::make($this->password)
                      ]);

        return $user;
    }

    public function createBook(int $record)
    {
        $user = $this->create_user();
        $faker = Factory::create('ja_JP');
        for($i=1; $i<=$record; $i++){
            Book::create([
                'title' => 'ワンピース',
                'user_id' => $user->id,
                'url' => 'sample.com',
                'detail' => $faker->realText($maxNbChars = 15),
                'review' => $faker->realText($maxNbChars = 30),
                'reviewer' => $user->name,
            ]);
        }
        for($i=1; $i<=$record; $i++){
            Book::create([
                'title' => 'NARUTO',
                'user_id' => $user->id,
                'url' => 'sample.com',
                'detail' => $faker->realText($maxNbChars = 15),
                'review' => $faker->realText($maxNbChars = 30),
                'reviewer' => $user->name,
            ]);
        }
    }

    public function test_本を取得できるか？(): void
    {
        $this->createBook(100);
        $books = $this->get('/api/public/books');
        $books->assertStatus(200);
    }

    public function test_検索処理を実行できるか？()
    {
        $this->createBook(5000);
        $books = $this->get('/api/public/books?title_keyword=NARUTO');
        $books->assertJsonMissing(["title" => "ワンピース"]);
    }
}
