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
        $books = Book::all();
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
}
