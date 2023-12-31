<?php

namespace Tests\Feature\App\Http\Controllers\Book;

use App\Models\Book;
use App\Models\BookComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function createBook()
    {
        $this->createUser();
        Book::factory()->count(100)->create();
        Book::factory()->count(100)->create([ 'title' => 'NARUTO' ]);
    }

    public function test_本を取得できるか？(): void
    {
        $this->createBook();
        $books = $this->get('/api/books')->json();
        $this->assertCount(10, $books);
    }

    public function test_検索処理を実行できるか？()
    {
        $this->createBook();
        $books = $this->get('/api/books?title_keyword=NARUTO');
        $books->assertJsonMissing(["title" => "ワンピース"]);
    }

    public function test_新規の書籍レビューを作れるか？()
    {
        $this->assertDatabaseCount('books', 0);

        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $this->post('/api/books', [
            'title' => fake()->realtext(10),
            'user_id' => $user->id,
            'url' => 'sample.com',
            'detail' => fake()->realText(15),
            'review' => fake()->realText(30),
            'reviewer' => $user->name,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertDatabaseCount('books', 1);
    }

    public function test_書籍の更新ができるか？()
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $book = Book::create([
            'title' => 'ドラゴンボール',
            'user_id' => $user->id,
            'url' => 'sample.com',
            'detail' => 'バトル漫画',
            'review' => 'バトル漫画です',
            'reviewer' => $user->name,
        ]);

        $update_book_data = [
            'title' => 'SLUM DUNK',
            'url' => 'slumdunk.com',
            'detail' => 'バスケ漫画',
            'review' => 'バスケ漫画です',
        ];

        $this->put("/api/books/{$book->id}", $update_book_data, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $update_books = $this->get("/api/books/{$book->id}", [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $update_books->assertJsonMissing([
            'title' => 'ドラゴンボール',
            'url' => 'sample.com',
            'detail' => 'バトル漫画',
            'review' => 'バトル漫画です',
        ]);
        $update_books->assertJson($update_book_data);
    }

    public function test_書籍を削除できるか？()
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $book = Book::create([
            'title' => 'ドラゴンボール',
            'user_id' => $user->id,
            'url' => 'sample.com',
            'detail' => 'バトル漫画',
            'review' => 'バトル漫画です',
            'reviewer' => $user->name,
        ]);
        $this->assertDatabaseCount('books', 1);

        $this->delete("/api/books/{$book->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_コメントを取得できるか？()
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $book = Book::create([
            'title' => 'ドラゴンボール',
            'user_id' => $user->id,
            'url' => 'sample.com',
            'detail' => 'バトル漫画',
            'review' => 'バトル漫画です',
            'reviewer' => $user->name,
        ]);

        $create_comment_response = BookComment::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'comment' => '良いレビューですね！',
            'comment_likes' => 0,
        ]);

        $response = $this->get("/api/books/{$book->id}/comment", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([[
            'id' => $create_comment_response->id,
            'user_name' => $create_comment_response->user->name,
            'user_image_url' => $create_comment_response->user->image_url,
            'comment' => urldecode($create_comment_response->comment),
            'comment_likes' => 0,
            'is_reviewer' => true,
            'is_your_comment' => true
        ]]);
    }

    public function test_レビューに対してのコメントを作れるか？()
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $book = Book::create([
            'title' => 'ドラゴンボール',
            'user_id' => $user->id,
            'url' => 'sample.com',
            'detail' => 'バトル漫画',
            'review' => 'バトル漫画です',
            'reviewer' => $user->name,
        ]);

        $response = $this->post("/api/books/{$book->id}/comment", [
            'comment' => '良いレビューですね！'
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'user_name' => $user->name,
            'user_image_url' => $user->image_url,
            'comment' => '良いレビューですね！',
            'comment_likes' => 0
        ]);
        $this->assertDatabaseCount('book_review_comment', 1);
        $this->assertDatabaseHas('book_review_comment', [
            'book_id' => $book->id,
        ]);
    }

    /**
     * @dataProvider fluctuationLikesProvider
     */
    public function test_コメントのいいねの増減が可能か？($fluctuation)
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $book = Book::create([
            'title' => 'ドラゴンボール',
            'user_id' => $user->id,
            'url' => 'sample.com',
            'detail' => 'バトル漫画',
            'review' => 'バトル漫画です',
            'reviewer' => $user->name,
        ]);

        $create_comment_response = BookComment::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'comment' => '良いレビューですね！',
            'comment_likes' => 1,
        ]);

        $response = $this->post('/api/comment/fluctuationLikes', [
            'comment_id' => $create_comment_response->id,
            'likes' => $fluctuation
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['comment_likes' => 1 + $fluctuation ]);
        $this->assertDatabaseHas('book_review_comment', [
            'comment_likes' => 1 + $fluctuation,
        ]);
    }

    public static function fluctuationLikesProvider()
    {
        return[
            'いいねの増加が可能か？' => [1],
            'いいねの減少が可能か？' => [-1],
        ];
    }

    public function test_コメントを編集できるか？()
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $book = Book::create([
            'title' => 'ドラゴンボール',
            'user_id' => $user->id,
            'url' => 'sample.com',
            'detail' => 'バトル漫画',
            'review' => 'バトル漫画です',
            'reviewer' => $user->name,
        ]);

        $book_review_comment = BookComment::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'comment' => '良いレビューですね！',
            'comment_likes' => 1,
        ]);

        $edit_comment_response = $this->patch("/api/books/$book_review_comment->id/comment", [
            'comment' => '更新したコメントだよ'
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $edit_comment_response->assertStatus(200);
        $edit_comment_response->assertJson([
            'user_name' => $user->name,
            'user_image_url' => $user->image_url,
            'comment' => '更新したコメントだよ',
            'comment_likes' => 1
        ]);
        $this->assertDatabaseHas('book_review_comment', [
            'comment' => '更新したコメントだよ',
        ]);
        $this->assertDatabaseMissing('book_review_comment', [
            'comment' => '良いレビューですね！',
        ]);
    }

    public function test_コメントを削除できるか？()
    {
        $user = $this->createUser();
        $token = $this->createToken($this->email, $this->password);

        $book = Book::create([
            'title' => 'ドラゴンボール',
            'user_id' => $user->id,
            'url' => 'sample.com',
            'detail' => 'バトル漫画',
            'review' => 'バトル漫画です',
            'reviewer' => $user->name,
        ]);

        $book_review_comment = BookComment::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'comment' => '良いレビューですね！',
            'comment_likes' => 1,
        ]);
        $this->assertDatabaseCount('book_review_comment', 1);

        $this->delete("/api/books/$book_review_comment->id/comment", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $this->assertDatabaseCount('book_review_comment', 0);
    }
}
