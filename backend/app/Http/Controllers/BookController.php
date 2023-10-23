<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Models\Book;
use App\Models\User;
use App\Models\Log;

class BookController extends Controller
{
    public function getBooks(Request $request)
    {
        $number = $request->query('offset');
        $books = Book::orderBy('id', 'desc')->skip($number)->take(10)->get(); //テーブルを10件ずつ取得する。
        $bookData = [];

        foreach ($books as $book) {
            $bookData[] = [
                'id' => $book->id,
                'title' => $book->title,
                'url' => $book->url,
                'detail' => $book->detail,
                'review' => $book->review,
                'reviewer' => $book->reviewer
            ];
        }
    
        return response()->json($bookData, 200, [], JSON_UNESCAPED_UNICODE);
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
                'reviewer' => $user_info->name,
                'token' => $user_info->token
            ]);

            DB::commit();

            return response()->json([
                'title' => $request->input('title'),
                'url' => $request->input('url'),
                'detail' => $request->input('detail'),
                'review' => $request->input('review'),
                'reviewer' => $user_info->name
            ], 200, [], JSON_UNESCAPED_UNICODE);
            ;
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['errormessage' => $e->getMessage() ]);
        }
    }

    public function getBookDatail(Request $request, $id)
    {
        $bookDatail = Book::findOrFail($id);
        $isMine = false;

        if($bookDatail->token === $request->headers->get('Authorization')) $isMine = true;

        return response()->json([
            'title' => $bookDatail->title,
            'url' => $bookDatail->url,
            'detail' => $bookDatail->detail,
            'review' => $bookDatail->review,
            'reviewer' => $bookDatail->reviewer,
            'isMine' => $isMine
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateBook(Request $request, $id)
    {
        $bookDatail = Book::findOrFail($id);
        DB::beginTransaction();

        try {
            $bookDatail->update([
                        'title' => $request->input('title'),
                        'url' => $request->input('url'),
                        'detail' => $request->input('detail'),
                        'review' => $request->input('review'),
                    ]);

            DB::commit();

            return response()->json([
                'id' => $bookDatail->id,
                'title' => $bookDatail->title,
                'url' => $bookDatail->url,
                'detail' => $bookDatail->detail,
                'review' => $bookDatail->review,
                'reviewer' => $bookDatail->reviewer
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['errormessage' => $e->getMessage() ]);
        }
    }

    public function setlog(Request $request)
    {
        $user_info = User::where('token', $request->headers->get('Authorization'))->first();

        DB::beginTransaction();

        try{
            Log::insert([
                'user_name' => $user_info->name,
                'user_email' => $user_info->email,
                'access_log' => 'http://127.0.0.1:3000/detail/'.$request->input('selectBookId')
            ]);

            DB::commit();

        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['errormessage' => $e->getMessage() ]);
        }
    }
}
