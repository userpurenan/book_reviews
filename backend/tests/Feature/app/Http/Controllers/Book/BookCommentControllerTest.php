<?php

declare(strict_types=1);

namespace Tests\Feature\App\Http\Controllers\Book;

use App\Models\Book;
use App\Models\BookComment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookCommentControllerTest extends TestCase
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

    public function test_レビューに対するコメントを取得することができる(): void
    {
        $book = Book::factory()->create();
        $book_review_comment = BookComment::factory()->create();

        $response = $this->get("/api/books/{$book->id}/comment", [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(200);

        /**
         * 実際のAPIがオブジェクトを一個ずつ配列に詰めて、それをjson形式に変換して返り値として返しているため、
         * その返り値に合わせるためにassertExactJson()の引数のデータ形式を、
         * [[]]
         * 👆このようにする。
         */
        $response->assertExactJson([[
            'id' => $book_review_comment->id,
            'user_name' => $book_review_comment->user->name,
            'user_image_url' => $book_review_comment->user->image_url,
            'comment' => $book_review_comment->comment,
            'comment_likes' => 0,
            'is_reviewer' => 1, //データベースからboolean型の値を取得しているのでtrueが１になる
            'is_your_comment' => true,
            "is_likes_comment" => false,
        ]]);
    }

    public function test_レビューに対してのコメントを作成することができる(): void
    {
        $book = Book::factory()->create();
        $comment = fake()->realText(15);

        $response = $this->post("/api/books/{$book->id}/comment", [
            'comment' => $comment
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'user_name' => $this->user->name,
            'user_image_url' => $this->user->image_url,
            'comment' => $comment,
            'comment_likes' => 0
        ]);
        $this->assertDatabaseHas('book_review_comments', [
            'user_id' => $this->user->id,
            'book_id' => $book->id,
            'comment' => $comment,
            'is_reviewer_comment' => 1, //データベースからboolean型の値を取得しているのでtrueが１になる
            'comment_likes' => 0
        ]);
    }

    /**
     * @dataProvider fluctuationLikesProvider
     */
    public function test_コメントのいいねの増減が可能(int $fluctuation): void
    {
        Book::factory()->create();

        //いいねの数は０より下回らないように設定しており、初期値が０のままだと減少しているのかわからなくなるので、このテストではいいねの数の初期値を１にする
        $book_review_comment = BookComment::factory()->create(['comment_likes' => 1,]);
        $update_comment_like_result = $book_review_comment->comment_likes + $fluctuation;

        $response = $this->post('/api/comment/updateLikes', [
            'comment_id' => $book_review_comment->id,
            'likes' => $fluctuation
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(200);
        $response->assertExactJson(['comment_likes' => $update_comment_like_result ]);
        $this->assertDatabaseHas('book_review_comments', [
            'comment_likes' => $update_comment_like_result,
        ]);
    }

    public static function fluctuationLikesProvider(): array
    {
        return[
            'いいねの数が増える' => [1],
            'いいねの数が減る' => [-1],
        ];
    }

    public function test_いいねが0を下回らない(): void
    {
        Book::factory()->create();
        $book_review_comment = BookComment::factory()->create();

        $response = $this->post('/api/comment/updateLikes', [
            'comment_id' => $book_review_comment->id,
            'likes' => -1
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(500);
        $response->assertExactJson(['error' => 'いいねは0未満にはできません' ]);
        $this->assertDatabaseHas('book_review_comments', [
            'comment_likes' => 0,
        ]);
    }

    public function test_コメントを編集することができる(): void
    {
        Book::factory()->create();
        $book_review_comment = BookComment::factory()->create();
        $update_comment = fake()->realText(10);

        $edit_comment_response = $this->patch("/api/books/{$book_review_comment->id}/comment", [
            'comment' => $update_comment
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $edit_comment_response->assertStatus(200);
        $edit_comment_response->assertExactJson([
            'user_name' => $this->user->name,
            'user_image_url' => $this->user->image_url,
            'comment' => $update_comment,
            'comment_likes' => 0
        ]);
        $this->assertDatabaseHas('book_review_comments', [
            'comment' => $update_comment,
        ]);
        $this->assertDatabaseMissing('book_review_comments', [
            'comment' => $book_review_comment->comment,
        ]);
    }

    public function test_コメントを削除することができる(): void
    {
        Book::factory()->create();
        $book_review_comment = BookComment::factory()->create();

        $response = $this->delete("/api/books/{$book_review_comment->id}/comment", [], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('book_review_comments', [
            'user_id' => $book_review_comment->user_id,
            'book_id' => $book_review_comment->book_id,
            'comment' => $book_review_comment->comment,
            'comment_likes' => 0
        ]);
    }
}
