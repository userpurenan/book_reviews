<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
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
        DB::beginTransaction();

        $user_id = Auth::id();
        $user_name = Auth::user()->name;
        try {
            Book::create([
                'title' => $request->input('title'),
                'user_id' => $user_id,
                'url' => $request->input('url'),
                'detail' => $request->input('detail'),
                'review' => $request->input('review'),
                'reviewer' => $user_name
            ]);

            DB::commit();

            return response()->json([
                'title' => $request->input('title'),
                'url' => $request->input('url'),
                'detail' => $request->input('detail'),
                'review' => $request->input('review'),
                'reviewer' => $user_name,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['errormessage' => $e->getMessage() ]);
        }
    }

    public function getBookDatail($id)
    {
        $bookDatail = Book::findOrFail($id);
        $isMine = false;

        if($bookDatail->user_id === Auth::id()) $isMine = true;

        return response()->json([
            'title' => $bookDatail->title,
            'url' => $bookDatail->url,
            'detail' => $bookDatail->detail,
            'review' => $bookDatail->review,
            'reviewer' => $bookDatail->reviewer,
            'isMine' => $isMine,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateBook(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $bookDatail = Book::findOrFail($id);
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
        $user_id = Auth::id();
        DB::beginTransaction();

        try{
            $log = Log::create([
                        'user_id' => $user_id,
                        'access_log' => 'http://127.0.0.1:3000/detail/'.$request->input('selectBookId')
                    ]);

            DB::commit();

            return response()->json(['log' => $log->access_log ]);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['errormessage' => $e->getMessage() ]);
        }
    }
}
