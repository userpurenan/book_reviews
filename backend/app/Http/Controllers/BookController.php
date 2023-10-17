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
        $number = $request->query('offset');
        $books = Book::skip($number - 1)->take(10)->get();;
        return response()->json($books, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function createBooks(Request $request)
    {
        $user_info = User::where('token', $request->headers->get('Authorization'))->first();
        DB::beginTransaction();

        try {
            Book::insert([
                'title' => $request->input('title'),
                'url' => $request->input('url'),
                'detail' => $request->input('detail'),
                'review' => $request->input('review'),
                'reviewer' => $user_info->name
            ]);

            DB::commit();

            return response()->json([
                'title' => $request->input('title'),
                'url' => $request->input('url'),
                'detail' => $request->input('detail'),
                'review' => $request->input('review'),
                'reviewer' => $user_info->name
            ],200, [], JSON_UNESCAPED_UNICODE);;
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['errormessage' => $e ]);
        }
    }

    public function getBookDatail($id){
        $bookDatail = Book::find($id);

        return response()->json([
            'title' => $bookDatail->title,
            'url' => $bookDatail->url,
            'detail' => $bookDatail->detail,
            'review' => $bookDatail->review,
            'reviewer' => $bookDatail->reviewer
        ],200, [], JSON_UNESCAPED_UNICODE);;
    }
}
