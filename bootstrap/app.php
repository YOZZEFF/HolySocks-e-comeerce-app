<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::get('/bootstrap-test', function () {
                return 'bootstrap-test-ok';
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {

         $middleware->validateCsrfTokens(except: ['*']);

              $middleware->appendToGroup('api', [
            \App\Http\Middleware\SetLocale::class,
        ]);

          $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.active' => \App\Http\Middleware\CheckUserActive::class,
        ]);
        //
    //      $middleware->appendToGroup('api', [
    //     EnsureFrontendRequestsAreStateful::class,
    // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

          $exceptions->render(function (ValidationException $e) {

        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);
    });



    $exceptions->render(function (ModelNotFoundException $e) {

        return response()->json([
            'status'  => false,
            'message' => 'Resource not found',
        ], 404);
    });

    $exceptions->render(function (NotFoundHttpException $e) {

        return response()->json([
            'status'  => false,
            'message' => 'Route not found',
        ], 404);
    });
    })->create();
