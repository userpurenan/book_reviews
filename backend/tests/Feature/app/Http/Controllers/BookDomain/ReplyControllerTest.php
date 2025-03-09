<?php

declare(strict_types=1);

namespace Tests\Feature\App\Http\Controllers\BookDomain;

use Tests\TestCase;
use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use App\Models\BookDomain\Reply;
use App\Models\BookDomain\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReplyControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $password;
    private string $email;
    private $user;
    private $token;
    private $book;
    private $comment;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->token = $this->user->createToken('Token')->accessToken;

        // テスト用の書籍とコメントを作成
        $this->book = Book::factory()->create();
        $this->comment = Comment::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id
        ]);
    }

    /**
     * @test
     */
    public function 返信を新規作成できる(): void
    {
        $replyData = [
            'content' => 'テスト返信です',
            'comment_id' => $this->comment->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/comment/{$this->comment->id}/reply", $replyData);

        $response->assertStatus(201);

        $response->assertExactJson([
            'user_name' => $this->user->name,
            'user_image_url' => $this->user->image_url,
            'content' => 'テスト返信です',
            'likes' => 0
        ]);
    }

    /**
     * @test
     */
    public function 特定のコメントの返信一覧を取得できる(): void
    {
        Reply::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/comment/{$this->comment->id}/reply");

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function 返信を更新できる(): void
    {
        $content = Reply::factory()->create();

        $updateData = [
            'content' => '更新された返信内容'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/reply/{$content->id}", $updateData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'content' => '更新された返信内容'
            ]);

        $this->assertDatabaseHas('replies', [
            'content' => '更新された返信内容',
        ]);
        $this->assertDatabaseMissing('replies', [
            'content' => $content->reply,
        ]);
    }

    /**
     * @test
     */
    public function 返信を削除できる(): void
    {
        $content = Reply::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/reply/{$content->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('replies', ['id' => $content->id]);
    }

    /**
     * @test
     */
    public function 他のユーザーの返信は更新できない(): void
    {
        $content = Reply::factory()->create();
        $otherUser = User::factory()->create();
        $token = $otherUser->createToken('Token')->accessToken;

        $updateData = [
            'content' => '更新された返信内容'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/reply/{$content->id}", $updateData);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 他のユーザーの返信は削除できない(): void
    {
        $content = Reply::factory()->create();
        $otherUser = User::factory()->create();
        $token = $otherUser->createToken('Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->delete("/api/reply/{$content->id}");

        $response->assertStatus(403);
    }

    /**
     * @dataProvider updateLikesProvider
     * @test
     */
    public function 返信のいいねの増減が可能(int $update_likes): void
    {
        $reply = Reply::factory()->create(['likes' => 1]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
            ])->postJson("api/reply/$reply->id/updateLikes", [
                'likes' => $update_likes
            ]);

        $reply_likes = $reply->likes + $update_likes;

        $response->assertStatus(200);
        $response->assertExactJson(['likes' => $reply_likes ]);
        $this->assertDatabaseHas('replies', [
            'likes' => $reply->likes + $update_likes
        ]);
    }

    public static function updateLikesProvider(): array
    {
        return[
            'いいねの数が増える' => [1],
            'いいねの数が減る' => [-1],
        ];
    }
}
