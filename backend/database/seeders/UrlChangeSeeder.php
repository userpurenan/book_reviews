<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class UrlChangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::all();

        foreach($books as $book) {
            $book_search_response = Http::get("https://app.rakuten.co.jp/services/api/BooksTotal/Search/20170404?format=json&keyword=$book->title&booksGenreId=001&applicationId=1011475384486274392");
            $book_response_json = $book_search_response->json();
            $book->update(['url' => $book_response_json['Items'][0]['Item']['itemUrl']]);
            sleep(1); //楽天APIは1秒間で複数リクエストがあるとエラーを返すからカラム更新後に１秒待つ
        }
    }
}
