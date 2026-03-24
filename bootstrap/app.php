<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            if (str_contains($e->getMessage(), 'SQLSTATE[HY000] [2002]')) {
                return response()->view('errors.database', [], 500);
            }
        });
        $exceptions->render(function (\PDOException $e, \Illuminate\Http\Request $request) {
            if (str_contains($e->getMessage(), 'SQLSTATE[HY000] [2002]')) {
                return response()->view('errors.database', [], 500);
            }
        });
    })->create();
