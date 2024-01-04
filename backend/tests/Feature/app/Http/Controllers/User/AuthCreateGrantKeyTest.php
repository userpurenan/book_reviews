<?php

namespace Tests\Feature\App\Http\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthCreateGrantKeyTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_グラントキーがデータベースに保存されるか？(): void
    {
        $cmd = 'php artisan passport:install --env=testing';
        exec($cmd);

        $this->assertDatabaseHas('oauth_clients', [
            'name' => 'Laravel Password Grant Client',
        ]);
    }
}
