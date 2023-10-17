<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\User;

class BookController extends Controller
{
    public function getBooks(Request $request)
    {
        $user_info = User::where('token', $request->headers->get('Authorization'))->first();
        $books = Book::all();
        $bookList = [];

        foreach($books as $book) {
            $book_array = array(
                            'id' => $book->id,
                            'title' => $book->title,
                            'url' => $book->url,
                            'detail' => $book->detail,
                            'review' => $book->review,
                            'reviewer' => $book->reviewer
                          );

            $book_json = json_encode($book_array, JSON_UNESCAPED_UNICODE);
            $bookList[] = $book_json;
        }

        return $bookList;
    }
}
