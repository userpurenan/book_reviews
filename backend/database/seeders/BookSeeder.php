<?php

namespace Database\Seeders;

use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use Illuminate\Database\Seeder;
use App\Models\BookDomain\Comment;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(10)->create();
        Book::factory()->count(30)->create();
        Comment::factory()->count(100)->create();
    }
}
