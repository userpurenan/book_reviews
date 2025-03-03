<?php

declare(strict_types=1);

namespace Tests\Feature\App\Http\Controllers\BookDomain;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use App\Models\BookDomain\Reply;
use App\Models\BookDomain\Comment;
use Illuminate\Support\Facades\Hash;
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
        $this->email = fake()->safeEmail();
        $this->password = Str::random(10);

        $this->user = User::create([
            'name' => fake()->name(),
            'email' => $this->email,
            'password' => Hash::make($this->password)
        ]);

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
    public function test_返信を新規作成できる(): void
    {
        $replyData = [
            'reply' => 'テスト返信です',
            'comment_id' => $this->comment->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/comment/{$this->comment->id}/reply", $replyData);

        $response->assertStatus(201);

        $response->assertExactJson([
            'user_name' => $this->user->name,
            'user_image_url' => $this->user->image_url,
            'reply' => 'テスト返信です',
            'reply_likes' => 0
        ]);
    }

    /**
     * @test
     */
    public function test_特定のコメントの返信一覧を取得できる(): void
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
    public function test_返信を更新できる(): void
    {
        $content = Reply::factory()->create();

        $updateData = [
            'reply' => '更新された返信内容'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/comment/{$this->comment->id}/reply/{$content->id}", $updateData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'reply' => '更新された返信内容'
            ]);

        $this->assertDatabaseHas('reply', [
            'reply' => '更新された返信内容',
        ]);
        $this->assertDatabaseMissing('reply', [
            'reply' => $content->reply,
        ]);
    }

    /**
     * @test
     */
    public function test_返信を削除できる(): void
    {
        $content = Reply::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/comment/{$this->comment->id}/reply/{$content->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('reply', ['id' => $content->id]);
    }

    /**
     * @test
     */
    public function test_他のユーザーの返信は更新できない(): void
    {
        $content = Reply::factory()->create();
        $otherUser = User::factory()->create();
        $token = $otherUser->createToken('Token')->accessToken;

        $updateData = [
            'reply' => '更新された返信内容'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/comment/{$this->comment->id}/reply/{$content->id}", $updateData);

        $response->assertStatus(403);
    }
}
