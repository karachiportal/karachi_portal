<?php
// ════════════════════════════════════════════════════════
//  bootstrap/app.php  (Laravel 11 style)
// ════════════════════════════════════════════════════════

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin'     => \App\Http\Middleware\AdminMiddleware::class,
            'admin.web' => \App\Http\Middleware\AdminWebMiddleware::class,
        ]);

        $middleware->statefulApi();
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON for API auth exceptions
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login.',
                ], 401);
            }
        });

        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found.',
                ], 404);
            }
        });

        $exceptions->render(function (
            \Illuminate\Validation\ValidationException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });
    })
    ->create();


// ════════════════════════════════════════════════════════
//  config/sanctum.php  — add to stateful domains
// ════════════════════════════════════════════════════════
/*
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
))),
'expiration' => null,   // tokens never expire (or set days e.g. 60*24*30)
*/


// ════════════════════════════════════════════════════════
//  .env  — required settings
// ════════════════════════════════════════════════════════
/*
APP_NAME="Commissioner Karachi Portal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-server.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=karachi_portal
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_pass

FILESYSTEM_DISK=public

SANCTUM_STATEFUL_DOMAINS=your-domain.com

# Optional: Firebase for push notifications
FIREBASE_SERVER_KEY=your_firebase_key
*/
