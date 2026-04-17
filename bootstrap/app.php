<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ping DB and reconnect if Railway's proxy dropped the connection
        $middleware->web(prepend: [
            \App\Http\Middleware\EnsureDatabaseConnection::class,
        ]);
        $middleware->api(prepend: [
            \App\Http\Middleware\EnsureDatabaseConnection::class,
        ]);

        // Register no-cache headers on ALL web responses
        $middleware->web(append: [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->alias([
            'role'       => \App\Http\Middleware\RoleMiddleware::class,
            'auth.check' => \App\Http\Middleware\AuthenticatedSessionCheck::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // FIX 3 — CSRF token expired (419): redirect back with friendly message
        $exceptions->render(function (TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()
                ->back()
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('error', 'Your session expired. Please try submitting the form again.');
        });

        // FIX 8 — 404 Not Found: redirect to dashboard instead of crashing
        $exceptions->render(function (NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('dashboard')
                ->with('error', 'The page you were looking for was not found.');
        });

        // FIX 8 — Method Not Allowed (back button hits a POST-only route via GET)
        $exceptions->render(function (MethodNotAllowedHttpException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('dashboard')
                ->with('error', 'Invalid navigation detected. Redirected to dashboard.');
        });

        // FIX 8 — Authentication errors (unauthenticated access)
        $exceptions->render(function (AuthenticationException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('login')
                ->with('error', 'Please log in to continue.');
        });

        // Handle MySQL server has gone away (2006) — reconnect and redirect back cleanly
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            $msg = $e->getMessage();
            if (str_contains($msg, '[2006]') || str_contains($msg, 'server has gone away')) {
                try { \Illuminate\Support\Facades\DB::reconnect(); } catch (\Exception $_) {}
                return redirect()->back()
                    ->withInput($request->except('password', 'password_confirmation', '_token'))
                    ->with('error', 'Connection was briefly interrupted. Please try again.');
            }
            if (str_contains($msg, 'SQLSTATE[HY000] [2002]')) {
                return response()->view('errors.database', [], 500);
            }
        });
        $exceptions->render(function (\PDOException $e, \Illuminate\Http\Request $request) {
            if (str_contains($e->getMessage(), 'SQLSTATE[HY000] [2002]')) {
                return response()->view('errors.database', [], 500);
            }
        });
    })->create();

