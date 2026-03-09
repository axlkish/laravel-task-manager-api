<?php

use Dotenv\Dotenv;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: env('APP_API_PREFIX', 'api/v1'),
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Validation failed',
                        'details' => $e->errors(),
                    ]
                ], 422);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Resource not found'
                    ]
                ], 404);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {

            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Resource not found'
                    ]
                ], 404);
            }

        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Server error'
                    ]
                ], 500);
            }
        });
    })->create();
