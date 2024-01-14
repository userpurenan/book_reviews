<?php

declare(strict_types=1);

namespace Tests\Feature\App\Http\Controllers\Book;

use App\Models\Book;
use App\Models\BookComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCommentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $cmd = 'php artisan passport:install --env=testing';
        exec($cmd);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $cmd = 'php artisan migrate:refresh --env=testing';
        exec($cmd);
    }

    public function test_レビューに対するコメントを取得することができる(): void
    {
        $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $book = Book::factory()->create();
        $book_review_comment = BookComment::factory()->create();

        $response = $this->get("/api/books/{$book->id}/comment", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([[
            'id' => $book_review_comment->id,
            'user_name' => $book_review_comment->user->name,
            'user_image_url' => $book_review_comment->user->image_url,
            'comment' => urldecode($book_review_comment->comment),
            'comment_likes' => 0,
            'is_reviewer' => true,
            'is_your_comment' => true
        ]]);
    }

    public function test_レビューに対してのコメントを作成することができる(): void
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $book = Book::factory()->create();
        $comment = fake()->realText(15);

        $response = $this->post("/api/books/{$book->id}/comment", [
            'comment' => $comment
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'user_name' => $user->name,
            'user_image_url' => $user->image_url,
            'comment' => $comment,
            'comment_likes' => 0
        ]);
        $this->assertDatabaseHas('book_review_comment', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'comment' => $comment,
            'comment_likes' => 0
        ]);
    }

    /**
     * @dataProvider fluctuationLikesProvider
     */
    public function test_コメントのいいねの増減が可能(int $fluctuation): void
    {
        $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        Book::factory()->create();

        //いいねの数は０より下回らないように設定しており、初期値が０のままだと減少しているのかわからなくなるので、このテストではいいねの数の初期値を１にする
        $book_review_comment = BookComment::factory()->create(['comment_likes' => 1,]);
        $update_comment_like_result = $book_review_comment->comment_likes + $fluctuation;

        $response = $this->post('/api/comment/updateLikes', [
            'comment_id' => $book_review_comment->id,
            'likes' => $fluctuation
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertExactJson(['comment_likes' => $update_comment_like_result ]);
        $this->assertDatabaseHas('book_review_comment', [
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
        $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        Book::factory()->create();
        $book_review_comment = BookComment::factory()->create();

        $response = $this->post('/api/comment/updateLikes', [
            'comment_id' => $book_review_comment->id,
            'likes' => -1
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertExactJson(['comment_likes' => 0 ]);
        $this->assertDatabaseHas('book_review_comment', [
            'comment_likes' => 0,
        ]);
    }

    public function test_コメントを編集することができる(): void
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        Book::factory()->create();
        $book_review_comment = BookComment::factory()->create();
        $update_comment = fake()->realText(10);

        $edit_comment_response = $this->patch("/api/books/{$book_review_comment->id}/comment", [
            'comment' => $update_comment
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $edit_comment_response->assertStatus(200);
        $edit_comment_response->assertExactJson([
            'user_name' => $user->name,
            'user_image_url' => $user->image_url,
            'comment' => $update_comment,
            'comment_likes' => 0
        ]);
        $this->assertDatabaseHas('book_review_comment', [
            'comment' => $update_comment,
        ]);
        $this->assertDatabaseMissing('book_review_comment', [
            'comment' => $book_review_comment->comment,
        ]);
    }

    public function test_コメントを削除することができる(): void
    {
        $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        Book::factory()->create();
        $book_review_comment = BookComment::factory()->create();

        $response = $this->delete("/api/books/{$book_review_comment->id}/comment", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('book_review_comment', [
            'user_id' => $book_review_comment->user_id,
            'book_id' => $book_review_comment->book_id,
            'comment' => $book_review_comment->comment,
            'comment_likes' => 0
        ]);
    }
}
