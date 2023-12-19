<?php

namespace Tests\Feature;

use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_会員登録できるか？(): void
    {
        $faker = Factory::create('ja_JP');
        $response = $this->post('/api/signup',[
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Str::random(10)
        ]);

        $response->assertStatus(200);
    }
}
