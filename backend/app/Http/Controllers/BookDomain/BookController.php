<?php

declare(strict_types=1);

namespace App\Http\Controllers\BookDomain;

use Illuminate\Http\Request;
use App\Models\BookDomain\Book;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\BookRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Services\Book\BookLikeService;
use App\Models\UserDomain\UserReviewLikes;

class BookController extends Controller
{
    public function getBooks(BookRequest $request): JsonResponse
    {
        $number = $request->query('offset', $default = 0);

        // 書籍を10件取得している。
        // BookSearchメソッドはモデルに定義しているスコープ。キーワードに合致する書籍を取得してくる。
        $books = Book::BookSearch($request->query('title_keyword', $default = ''))->offset($number)->limit(10)->orderBy('id', 'desc')->get();

        return response()->json($books, 200);
    }

    public function getHotReview(): JsonResponse
    {
        $hot_review_top3 = Book::BookSearch()->limit(3)->orderBy('likes', 'desc')->get();

        return response()->json($hot_review_top3, 200);
    }

    public function updateReviewLikes(Request $request, int $book_id, BookLikeService $book_like): JsonResponse
    {
        $likes = (int) $request->input('likes');
        $update_likes_result = $book_like->updateLikes($book_id, $likes);

        if(isset($update_likes_result['error'])) {
            return response()->json($update_likes_result, 500);
        }

        return response()->json($update_likes_result, 200);
    }

    public function createBook(Request $request): JsonResponse
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

    public function getBookDatail(int $id): JsonResponse
    {
        $book_datail = Book::findOrFail($id);
        $is_review_likes = UserReviewLikes::where('user_id', Auth::id())->where('book_id', $id)->first();

        return response()->json([
            'title' => $book_datail->title,
            'url' => $book_datail->url,
            'detail' => $book_datail->detail,
            'review' => $book_datail->review,
            'reviewer' => $book_datail->user->name,
            'review_likes' => $book_datail->likes,
            'is_spoiler' => $book_datail->spoiler,
            'is_mine' => $book_datail->user_id === Auth::id() ? true : false,
            'is_review_likes' => $is_review_likes ? true : false,
        ], 200);
    }

    public function updateBook(Request $request, int $id, Book $book): JsonResponse
    {
        $book_datail = $book->findOrFail($id);
        Gate::authorize('auth_book', $book_datail);

        $book_datail->update([
                          'title' => $request->input('title') ?? $book_datail->title,
                          'url' => $request->input('url') ?? $book_datail->url,
                          'detail' => $request->input('detail') ?? $book_datail->detail,
                          'review' => $request->input('review') ?? $book_datail->review,
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

    public function deleteBook(Book $book, int $id): JsonResponse
    {
        $book_datail = $book->findOrFail($id);

        Gate::authorize('auth_book', $book_datail);

        $book_datail->delete();

        return response()->json([
            'message' => 'レビューの削除に成功しました'
        ], 200);
    }
}
