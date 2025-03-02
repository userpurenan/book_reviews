<?php

namespace App\Providers;

use App\Models\Book\Book;
use App\Models\Book\BookComment;
use App\Models\Book\Reply;
use App\Models\User\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('auth_book', function (User $user, Book $book) {
            return $user->id === $book->user_id;
        });

        Gate::define('auth_comment', function (User $user, BookComment $comment) {
            return $user->id === $comment->user_id;
        });

        Gate::define('auth_reply', function (User $user, Reply $reply) {
            return $user->id === $reply->user_id;
        });
    }
}
