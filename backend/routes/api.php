<?php

use Illuminate\Support\Facades\Route;
use App\Models\Book;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookController;

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
Route::get('/public/books', [BookController::class, 'getBooks']);

Route::middleware('auth:api')->group(function () {
    Route::post('/uploads', [UserController::class, 'imageUploads']);
    Route::patch('/users', [UserController::class, 'editUser']);
    Route::get('/users', [UserController::class, 'getUser']);
    Route::get('/books', [BookController::class, 'getBooks']);
    Route::post('/books', [BookController::class, 'createBooks']);
    Route::get('/books/{id}', [BookController::class, 'getBookDatail']);
    Route::put('/books/{id}', [BookController::class, 'updateBook']);
    Route::delete('/books/{id}', function ($id) {
        Book::findOrFail($id)->delete();

        return 'delete success!!';
    });
    Route::get('/books/{id}/comment', [BookController::class, 'getBookReviewComment']);
    Route::post('/books/{id}/comment', [BookController::class, 'createComment']);
    Route::post('/comment/fluctuationLikes', [BookController::class, 'fluctuationLikes']);
    Route::post('/logs', [BookController::class, 'setlog']);
});
