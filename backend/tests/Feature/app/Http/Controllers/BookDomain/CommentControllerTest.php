<?php

declare(strict_types=1);

namespace Tests\Feature\App\Http\Controllers\BookDomain;

use Tests\TestCase;
use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use App\Models\BookDomain\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

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
            'content' => $book_review_comment->content,
            'likes' => 0,
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
            'content' => $comment
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'user_name' => $this->user->name,
            'user_image_url' => $this->user->image_url,
            'content' => $comment,
            'likes' => 0
        ]);
        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'content' => $comment,
            'is_reviewer_comment' => 1, //データベースからboolean型の値を取得しているのでtrueが１になる
            'likes' => 0
        ]);
    }

    /**
     * @dataProvider updateLikesProvider
     * @test
     */
    public function コメントのいいねの増減が可能(string $like_action, int $like_change): void
    {
        //いいねの数は０より下回らないように設定しており、初期値が０のままだと減少しているのかわからなくなるので、このテストではいいねの数の初期値を１にする
        $comment = Comment::factory()->create(['likes' => 1]);
        $update_comment_like_result = $comment->likes + $like_change;

        $response = $this->post("/api/comment/{$comment->id}/{$like_action}", [], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(200);
        $response->assertExactJson(['likes' => $update_comment_like_result ]);
        $this->assertDatabaseHas('comments', [
            'likes' => $update_comment_like_result,
        ]);
    }

    public static function updateLikesProvider(): array
    {
        return[
            'いいねの数が増える' => ['incrementLikes', 1],
            'いいねの数が減る' => ['decrementLikes', -1],
        ];
    }

    /**
     * @test
     */
    public function いいねが0を下回らない(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->post("/api/comment/{$comment->id}/decrementLikes", [], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(500);
        $response->assertExactJson(['error' => 'いいねは0未満にはできません' ]);
        $this->assertDatabaseHas('comments', [
            'likes' => 0,
        ]);
    }

    /**
     * @test
     */
    public function コメントを編集することができる(): void
    {
        $book_review_comment = Comment::factory()->create();
        $update_comment = fake()->realText(10);

        $edit_comment_response = $this->patch("/api/comment/{$book_review_comment->id}", [
            'content' => $update_comment
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $edit_comment_response->assertStatus(200);
        $edit_comment_response->assertExactJson([
            'user_name' => $this->user->name,
            'user_image_url' => $this->user->image_url,
            'content' => $update_comment,
            'likes' => 0
        ]);
        $this->assertDatabaseHas('comments', [
            'content' => $update_comment,
        ]);
        $this->assertDatabaseMissing('comments', [
            'content' => $book_review_comment->comment,
        ]);
    }

    /**
     * @test
     */
    public function コメントを削除することができる(): void
    {
        $book_review_comment = Comment::factory()->create();

        $response = $this->delete("/api/comment/{$book_review_comment->id}", [], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', [
            'user_id' => $book_review_comment->user_id,
            'book_id' => $book_review_comment->book_id,
            'content' => $book_review_comment->comment,
            'likes' => 0
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
            'content' => 'テストのコメントです',
            'is_reviewer_comment' => $this->book->user_id === $this->user->id ? 1 : 0,
            'likes' => 0
        ]);

        $other_user = User::factory()->create();

        $token = $other_user->createToken('Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->patchJson("api/comment/$comment->id", [
            'content' => '更新したいコメント'
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
            'content' => 'テストのコメントです',
            'is_reviewer_comment' => $this->book->user_id === $this->user->id ? 1 : 0,
            'likes' => 0
        ]);

        $other_user = User::factory()->create();

        $token = $other_user->createToken('Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->delete("api/comment/$comment->id");

        $response->assertStatus(403);
    }
}
