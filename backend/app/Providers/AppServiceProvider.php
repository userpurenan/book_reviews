<?php

namespace App\Providers;

use App\Models\BookDomain\Book;
use App\Models\UserDomain\User;
use App\Models\BookDomain\Reply;
use App\Models\BookDomain\Comment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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

        Gate::define('auth_comment', function (User $user, Comment $comment) {
            return $user->id === $comment->user_id;
        });

        Gate::define('auth_reply', function (User $user, Reply $reply) {
            return $user->id === $reply->user_id;
        });
    }
}
