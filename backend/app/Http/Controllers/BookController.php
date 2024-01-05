<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
use App\Models\BookComment;


class BookController extends Controller
{
    public function getBooks(BookRequest $request)
    {
        $number = $request->query('offset');
        $books = Book::where("title", "LIKE", "%{$request->query('title_keyword')}%")
                       ->offset($number)->limit(10)->orderBy('id', 'desc')->get();

        $book_data = [];
        foreach ($books as $book) {
            $book_data[] = [
                'id' => $book->id,
                'title' => $book->title,
                'url' => $book->url,
                'detail' => $book->detail,
                'review' => $book->review,
                'reviewer' => $book->reviewer
            ];
        }

        return response()->json($book_data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function createBooks(Request $request)
    {
        $user_id = Auth::id();
        $user_name = Auth::user()->name;
        $book = Book::create([
                    'title' => $request->input('title'),
                    'user_id' => $user_id,
                    'url' => $request->input('url'),
                    'detail' => $request->input('detail'),
                    'review' => $request->input('review'),
                    'reviewer' => $user_name
                ]);

        return response()->json([
            'title' => $book->title,
            'url' => $book->url,
            'detail' => $book->detail,
            'review' => $book->review,
            'reviewer' => $user_name,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function getBookDatail($id)
    {
        $book_datail = Book::findOrFail($id);
        $isMine = false;

        if($book_datail->user_id === Auth::id()) $isMine = true;

        return response()->json([
            'title' => $book_datail->title,
            'url' => $book_datail->url,
            'detail' => $book_datail->detail,
            'review' => $book_datail->review,
            'reviewer' => $book_datail->reviewer,
            'isMine' => $isMine,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function createComment(Request $request, $id)
    {
        $books_review_comment = BookComment::create([
                                    'user_id' => Auth::id(),
                                    'book_id' => $id,
                                    'comment' => $request->input('comment'),
                                    'comment_likes' => 0,
                                ]);

        return response()->json([
                    'user_name' => $books_review_comment->user->name,
                    'user_image_url' => $books_review_comment->user->image_url,
                    'comment' => $books_review_comment->comment,
                    'comment_likes' => $books_review_comment->comment_likes
                ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function fluctuationLikes(Request $request)
    {
        $likes_count_change = $request->input('likes');
        $comment = BookComment::findOrFail($request->input('comment_id'));
        $comment_likes_count = $comment->comment_likes + $likes_count_change;
        $comment->update(['comment_likes' => $comment_likes_count ]);

        return response()->json([
            'comment_likes' => $comment->comment_likes
        ], 200);
    }

    public function getBookReviewComment(Request $request, $id)
    {
        $number = $request->query('comment_offset');

        $books_review_comment = BookComment::where('book_id', $id)->offset($number)->limit(10)->orderBy('id', 'desc')->get();

        $review_comment = [];
        foreach ($books_review_comment as $books_review) {
            $isReviewer = false;
            if($books_review->book->user_id == $books_review->user_id){
                $isReviewer = true;
            }
            $review_comment[] = [
                'id' => $books_review->id,
                'user_name' => $books_review->user->name,
                'user_image_url' => $books_review->user->image_url,
                'comment' => $books_review->comment,
                'comment_likes' => $books_review->comment_likes,
                'isReviewer' => $isReviewer
            ];
        }

        return response()->json($review_comment, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateBook(Request $request, $id)
    {
        $book_datail = Book::findOrFail($id);
        $book_datail->update([
                          'title' => $request->input('title'),
                          'url' => $request->input('url'),
                          'detail' => $request->input('detail'),
                          'review' => $request->input('review'),
                    ]);

        return response()->json([
              'id' => $book_datail->id,
              'title' => $book_datail->title,
              'url' => $book_datail->url,
              'detail' => $book_datail->detail,
              'review' => $book_datail->review,
              'reviewer' => $book_datail->reviewer
          ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
