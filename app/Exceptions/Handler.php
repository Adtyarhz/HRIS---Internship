<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

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

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle CSRF Token Mismatch (419 error)
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'CSRF token mismatch. Please refresh the page.',
                    'error' => 'TOKEN_MISMATCH'
                ], 419);
            }

            // Return custom 419 error page
            return response()->view('errors.419', [], 419);
        });

        // Handle Authentication Exception
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error' => 'UNAUTHENTICATED'
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        });

        // Handle Model Not Found
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found.',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        // Handle Not Found HTTP Exception
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Page not found.',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        // Handle Access Denied
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access denied.',
                    'error' => 'ACCESS_DENIED'
                ], 403);
            }

            return response()->view('errors.403', [], 403);
        });
    }
} 