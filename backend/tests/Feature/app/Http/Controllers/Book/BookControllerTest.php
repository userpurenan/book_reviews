<?php

namespace Tests\Feature\App\Http\Controllers\Book;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookControllerTest extends TestCase
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

    public function test_書籍を10件ずつ取得できる(): void
    {
        Book::factory()->count(11)->create();
        $books = $this->get('/api/books')->json();
        $this->assertCount(10, $books);
    }

    public function test_検索処理を実行する事ができる(): void
    {
        Book::factory()->count(11)->create();
        Book::factory()->create([ 'title' => 'NARUTO']);
        $books = $this->get('/api/books?title_keyword=NARUTO');
        $books->assertJsonMissing(["title" => 'ワンピース']);
        $books->assertJsonFragment(['title' => 'NARUTO']);
    }

    public function test_新規の書籍レビューを作ることができる(): void
    {
        $title = fake()->realtext(10);
        $url = fake()->url();
        $detail = fake()->realText(15);
        $review = fake()->realText(30);

        $this->post('/api/books', [
            'title' => $title,
            'user_id' => $this->user->id,
            'url' => $url,
            'detail' => $detail,
            'review' => $review,
            'reviewer' => $this->user->name,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $this->assertDatabaseHas('books', [
            'title' => $title,
            'user_id' => $this->user->id,
            'url' => $url,
            'detail' => $detail,
            'review' => $review,
            'reviewer' => $this->user->name,
        ]);
    }

    public function test_書籍の更新が実行できる(): void
    {
        $title = fake()->realtext(10);
        $url = fake()->url();
        $detail = fake()->realText(15);
        $review = fake()->realText(30);

        $book = Book::factory()->create();
        $update_book_data = [
            'title' => $title,
            'url' => $url,
            'detail' => $detail,
            'review' => $review,
        ];

        $this->put("/api/books/{$book->id}", $update_book_data, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $update_books = $this->get("/api/books/{$book->id}", [
            'Authorization' => 'Bearer ' . $this->token,
        ]);
        $update_books->assertJsonMissing([
            'title' => $book->title,
            'url' => $book->url,
            'detail' => $book->detail,
            'review' => $book->review,
        ]);
        $update_books->assertJson($update_book_data);
    }

    public function test_書籍を削除する事ができる(): void
    {
        $book = Book::factory()->create();

        $this->delete("/api/books/{$book->id}", [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $this->assertDatabaseMissing('books', [
            'title' => $book->title,
            'user_id' => $this->user->id,
            'url' => $book->url,
            'detail' => $book->detail,
            'review' => $book->review,
            'reviewer' => $this->user->name,
        ]);
    }
}
