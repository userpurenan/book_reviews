<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookDomain\BookController;
use App\Http\Controllers\UserDomain\UserController;
use App\Http\Controllers\BookDomain\ReplyController;
use App\Http\Controllers\BookDomain\CommentController;
use App\Http\Controllers\UserDomain\AuthUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('unauthorized', function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Unauthorized'
    ], 401);
})->name('api.jwt.unauthorized');

Route::post('/signup', [UserController::class, 'signUp']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/books', [BookController::class, 'getBooks']);
Route::get('/books/hotReview', [BookController::class, 'getHotReview']);

Route::middleware('auth:api')->group(function () {
    Route::post('/upload', [AuthUserController::class, 'imageUploads']);
    Route::patch('/user', [AuthUserController::class, 'editUser']);
    Route::get('/user', [AuthUserController::class, 'getUser']);
    Route::delete('/user', [AuthUserController::class, 'deleteUser']);
    Route::post('/books', [BookController::class, 'createBook']);
    Route::post('/books/{id}/updateLikes', [BookController::class, 'updateReviewLikes']);
    Route::get('/books/{id}', [BookController::class, 'getBookDatail']);
    Route::put('/books/{id}', [BookController::class, 'updateBook']);
    Route::delete('/books/{id}', [BookController::class, 'deleteBook']);
    Route::get('/books/{book_id}/comment', [CommentController::class, 'getComment']);
    Route::post('/books/{book_id}/comment', [CommentController::class, 'createComment']);
    Route::patch('/books/{book_id}/comment/{comment_id}', [CommentController::class, 'editComment']);
    Route::delete('/books/{book_id}/comment/{comment_id}', [CommentController::class, 'deleteComment']);
    Route::post('/comment/updateLikes', [CommentController::class, 'updateLikes']);
    Route::get('/comment/{comment_id}/reply', [ReplyController::class, 'fetchReply']);
    Route::post('/comment/{comment_id}/reply', [ReplyController::class, 'createReply']);
    Route::put('/comment/{comment_id}/reply/{reply_id}', [ReplyController::class, 'updateReply']);
    Route::delete('/comment/{comment_id}/reply/{reply_id}', [ReplyController::class, 'deleteReply']);
    Route::post('/comment/{comment_id}/reply/{reply_id}/updateLikes', [ReplyController::class, 'updateLikes']);

});
