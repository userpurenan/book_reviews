<?php

declare(strict_types=1);

namespace Tests\Feature\App\Http\Controllers\BookDomain;

use Tests\TestCase;
use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use App\Models\BookDomain\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $password;

    private string $email;

    private $user;

    private $book;

    private $token;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();

        $this->token = $this->user->createToken('Token')->accessToken;
    }

    /**
     * @test
     */
    public function レビューに対するコメントを取得することができる(): void
    {
        $book_review_comment = Comment::factory()->create();

        $response = $this->get("/api/books/{$this->book->id}/comment", [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(200);

        /**
         * 実際のAPIがオブジェクトを一個ずつ配列に詰めて、それをjson形式に変換して返り値として返しているため、
         * その返り値に合わせるためにassertExactJson()の引数のデータ形式を、
         * [[]]　←このように二次元配列で表す。
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

    /**
     * @test
     */
    public function レビューに対してのコメントを作成することができる(): void
    {
        $comment = fake()->realText(15);

        $response = $this->post("/api/books/{$this->book->id}/comment", [
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
            'book_id' => $this->book->id,
            'comment' => $comment,
            'is_reviewer_comment' => 1, //データベースからboolean型の値を取得しているのでtrueが１になる
            'comment_likes' => 0
        ]);
    }

    /**
     * @dataProvider updateLikesProvider
     * @test
     */
    public function コメントのいいねの増減が可能(int $fluctuation): void
    {
        //いいねの数は０より下回らないように設定しており、初期値が０のままだと減少しているのかわからなくなるので、このテストではいいねの数の初期値を１にする
        $book_review_comment = Comment::factory()->create(['comment_likes' => 1]);
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
    public function いいねが0を下回らない(): void
    {
        $book_review_comment = Comment::factory()->create();

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

    /**
     * @test
     */
    public function コメントを編集することができる(): void
    {
        $book_review_comment = Comment::factory()->create();
        $update_comment = fake()->realText(10);

        $edit_comment_response = $this->patch("/api/books/{$this->book->id}/comment/{$book_review_comment->id}", [
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

    /**
     * @test
     */
    public function コメントを削除することができる(): void
    {
        $book_review_comment = Comment::factory()->create();

        $response = $this->delete("/api/books/{$this->book->id}/comment/{$book_review_comment->id}", [], [
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

    /**
     * @test
     */
    public function 他のユーザーのコメントを編集できない(): void
    {
        $comment = Comment::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'comment' => 'テストのコメントです',
            'is_reviewer_comment' => $this->book->user_id === $this->user->id ? 1 : 0,
            'comment_likes' => 0
        ]);

        $other_user = User::factory()->create();

        $token = $other_user->createToken('Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->patchJson("api/books/{$this->book->id}/comment/$comment->id", [
            'comment' => '更新したいコメント'
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 他のユーザーのコメントを削除できない(): void
    {
        $comment = Comment::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'comment' => 'テストのコメントです',
            'is_reviewer_comment' => $this->book->user_id === $this->user->id ? 1 : 0,
            'comment_likes' => 0
        ]);

        $other_user = User::factory()->create();

        $token = $other_user->createToken('Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->delete("api/books/{$this->book->id}/comment/$comment->id");

        $response->assertStatus(403);
    }
}
