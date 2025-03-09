<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookDomain\ReviewController;
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
Route::get('/books', [ReviewController::class, 'index']);
Route::get('/books/hotReview', [ReviewController::class, 'getHotReview']);

Route::middleware('auth:api')->group(function () {
    Route::post('/upload', [AuthUserController::class, 'imageUploads']);
    Route::patch('/user', [AuthUserController::class, 'editUser']);
    Route::get('/user', [AuthUserController::class, 'getUser']);
    Route::delete('/user', [AuthUserController::class, 'deleteUser']);
    Route::get('/books/{id}', [ReviewController::class, 'show']);
    Route::post('/books', [ReviewController::class, 'store']);
    Route::put('/books/{id}', [ReviewController::class, 'update']);
    Route::delete('/books/{id}', [ReviewController::class, 'destroy']);
    Route::post('/books/{id}/updateLikes', [ReviewController::class, 'updateLikes']);
    Route::get('/books/{book_id}/comment', [CommentController::class, 'index']);
    Route::post('/books/{book_id}/comment', [CommentController::class, 'store']);
    Route::patch('/comment/{comment_id}', [CommentController::class, 'update']);
    Route::delete('/comment/{comment_id}', [CommentController::class, 'destroy']);
    Route::post('/comment/updateLikes', [CommentController::class, 'updateLikes']);
    Route::get('/comment/{comment_id}/reply', [ReplyController::class, 'index']);
    Route::post('/comment/{comment_id}/reply', [ReplyController::class, 'store']);
    Route::put('/reply/{reply_id}', [ReplyController::class, 'update']);
    Route::delete('/reply/{reply_id}', [ReplyController::class, 'destroy']);
    Route::post('/reply/{reply_id}/updateLikes', [ReplyController::class, 'updateLikes']);
});
