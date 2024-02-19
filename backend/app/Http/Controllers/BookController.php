<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;

class BookController extends Controller
{
    public function getBooks(BookRequest $request)
    {
        $number = $request->query('offset', $default = 0);

        // 書籍を10件取得している。
        // BookSearchメソッドはモデルに定義しているスコープ。キーワードに合致する書籍を取得してくる。
        $books = Book::BookSearch($request->query('title_keyword', $default = ''), $number)->orderBy('id', 'desc')->get();

        return response()->json($books, 200);
    }

    public function createBook(Request $request)
    {
        $user_id = Auth::id();
        $user_name = Auth::user()->name;
        $book = Book::create([
                    'title' => $request->input('title'),
                    'user_id' => $user_id,
                    'url' => $request->input('url'),
                    'detail' => $request->input('detail'),
                    'review' => $request->input('review'),
                    'reviewer' => $user_name,
                    'spoiler' => $request->input('isSpoiler') ? 1 : 0
                ]);

        return response()->json([
            'title' => $book->title,
            'url' => $book->url,
            'detail' => $book->detail,
            'review' => $book->review,
            'reviewer' => $user_name,
            'spoiler' => $request->input('isSpoiler'),
        ], 200);
    }

    public function getBookDatail(int $id)
    {
        $book_datail = Book::findOrFail($id);
        $is_mine = false;
        if($book_datail->user_id === Auth::id()) {
            $is_mine = true;
        }

        return response()->json([
            'title' => $book_datail->title,
            'url' => $book_datail->url,
            'detail' => $book_datail->detail,
            'review' => $book_datail->review,
            'reviewer' => $book_datail->user->name,
            'is_spoiler' => $book_datail->spoiler,
            'is_mine' => $is_mine,
        ], 200);
    }

    public function updateBook(Request $request, int $id)
    {
        $book_datail = Book::findOrFail($id);
        $book_datail->update([
                          'title' => $request->input('title'),
                          'url' => $request->input('url'),
                          'detail' => $request->input('detail'),
                          'review' => $request->input('review'),
                          'spoiler' => $request->input('isSpoiler') ? 1 : 0
                    ]);

        return response()->json([
              'id' => $book_datail->id,
              'title' => $book_datail->title,
              'url' => $book_datail->url,
              'detail' => $book_datail->detail,
              'review' => $book_datail->review,
              'reviewer' => $book_datail->reviewer,
              'spoiler' => $request->input('isSpoiler')
          ], 200);
    }

    public function deleteBook(int $id)
    {
        Book::findOrFail($id)->delete();
    }
}
