<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    // public function render($request, Throwable $exception)
    // {
    //     return parent::render($request, $exception);
    // }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // $this->renderable(function (NotFoundHttpException $exception, Request $request) {
        //     if ($request->is('api/*')) {
        //         return response()->json([
        //             'message' => $exception->getMessage()
        //         ], 404, [], JSON_UNESCAPED_UNICODE);
        //     }
        // });

        // $this->renderable(function (BadRequestHttpException $exception, Request $request) {
        //     if ($request->is('api/*')) {
        //         return response()->json([
        //             'message' => $exception->getMessage()
        //         ], 400, [], JSON_UNESCAPED_UNICODE);
        //     }
        // });
    }

}
