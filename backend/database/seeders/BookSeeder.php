<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book\Book;
use App\Models\Book\BookComment;
use App\Models\User\User;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(10)->create();
        Book::factory()->count(30)->create();
        BookComment::factory()->count(100)->create();
    }
}
