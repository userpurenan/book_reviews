<?php

use App\Http\Controllers\User\AuthUserController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Book\BookCommentController;
use App\Http\Controllers\Book\BookController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/books/{id}/comment', [BookCommentController::class, 'getComment']);
    Route::post('/books/{id}/comment', [BookCommentController::class, 'createComment']);
    Route::patch('/books/{id}/comment', [BookCommentController::class, 'editComment']);
    Route::delete('/books/{id}/comment', [BookCommentController::class, 'deleteComment']);
    Route::post('/comment/updateLikes', [BookCommentController::class, 'updateLikes']);
});
