<?php

namespace Tests\Feature\App\Http\Controllers\BookDomain;

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

    /**
     * @test
     */
    public function 書籍を10件ずつ取得できる(): void
    {
        Book::factory()->count(11)->create();
        $books = $this->get('/api/books')->json();
        $this->assertCount(10, $books);
    }

    /**
     * @test
     */
    public function 検索処理を実行する事ができる(): void
    {
        Book::factory()->count(11)->create();
        Book::factory()->create([ 'title' => 'NARUTO']);
        $books = $this->get('/api/books?title_keyword=NARUTO');
        $books->assertJsonMissing(["title" => 'ワンピース']);
        $books->assertJsonFragment(['title' => 'NARUTO']);
    }

    /**
     * @test
     */
    public function 新規の書籍レビューを作ることができる(): void
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

    /**
     * @test
     */
    public function 書籍の更新が実行できる(): void
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

    /**
     * @test
     */
    public function 書籍を削除する事ができる(): void
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

    /**
     * @dataProvider updateLikesProvider
     * @test
     */
    public function レビューのいいねの増減が可能(int $update_likes): void
    {
        $book_review = Book::factory()->create(['likes' => 1]);

        $response = $this->post("/api/books/{$book_review->id}/updateLikes", [
            'likes' => $update_likes
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $update_likes = $book_review->likes + $update_likes;
        $response->assertStatus(200);
        $response->assertExactJson(['review_likes' => $update_likes ]);
        $this->assertDatabaseHas('books', [
            'likes' => $update_likes
        ]);
    }

    public static function updateLikesProvider(): array
    {
        return[
            'いいねの数が増える' => [1],
            'いいねの数が減る' => [-1],
        ];
    }


    /**
     * @test
     */
    public function 他のユーザーのレビューを編集できない(): void
    {
        $book = Book::factory()->create();

        $other_user = User::factory()->create();

        $token = $other_user->createToken('Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->putJson("api/books/$book->id", [
            'comment' => '更新したいコメント'
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 他のユーザーのレビューを削除できない(): void
    {
        $book = Book::factory()->create();

        $other_user = User::factory()->create();

        $token = $other_user->createToken('Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->delete("api/books/$book->id");

        $response->assertStatus(403);
    }
}
