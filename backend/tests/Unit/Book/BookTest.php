<?php

namespace Tests\Unit\Book;

use App\Models\Book;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function createBook(int $record)
    {
        $user = $this->createUser();
        $faker = Factory::create('ja_JP');
        for($i=1; $i<=$record; $i++){
            Book::create([
                'title' => 'ワンピース',
                'user_id' => $user->id,
                'url' => 'sample.com',
                'detail' => $faker->realText(15),
                'review' => $faker->realText(30),
                'reviewer' => $user->name,
            ]);
        }
        for($i=1; $i<=$record; $i++){
            Book::create([
                'title' => 'NARUTO',
                'user_id' => $user->id,
                'url' => 'sample.com',
                'detail' => $faker->realText(15),
                'review' => $faker->realText(30),
                'reviewer' => $user->name,
            ]);
        }
    }

    public function test_本を取得できるか？(): void
    {
        $this->createBook(100);
        $books = $this->get('/api/public/books')->json();
        $this->assertCount(10, $books);
    }

    public function test_検索処理を実行できるか？()
    {
        $this->createBook(5000);
        $books = $this->get('/api/public/books?title_keyword=NARUTO');
        $books->assertJsonMissing(["title" => "ワンピース"]);
    }

    public function test_新規の書籍レビューを作れるか？()
    {
        $books = $this->get('/api/public/books')->json();
        $this->assertCount(0, $books);

        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);
        $faker = Factory::create('ja_JP');

        $this->post('/api/books', [
            'title' => $faker->realtext(10),
            'user_id' => $user->id,
            'url' => 'sample.com',
            'detail' => $faker->realText(15),
            'review' => $faker->realText(30),
            'reviewer' => $user->name,
        ],[
            'Authorization' => 'Bearer '.$token,
        ]);

        $books = $this->get('/api/public/books')->json();
        $this->assertCount(1, $books);
    }
}
