<?php
// ════════════════════════════════════════════════════════
//  app/Http/Middleware/AdminWebMiddleware.php
//  Protects web admin routes (session-based auth)
// ════════════════════════════════════════════════════════
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminWebMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Please login to access the admin panel.');
        }

        if (!in_array(Auth::user()->role, ['admin', 'staff'])) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->with('error', 'Access denied. Admin only.');
        }

        return $next($request);
    }
}


// ════════════════════════════════════════════════════════
//  bootstrap/app.php  — add admin.web middleware alias
//  Add this inside ->withMiddleware(function (Middleware $m) {
// ════════════════════════════════════════════════════════
/*
    $middleware->alias([
        'admin'     => \App\Http\Middleware\AdminMiddleware::class,      // for API
        'admin.web' => \App\Http\Middleware\AdminWebMiddleware::class,   // for Web
    ]);
*/


// ════════════════════════════════════════════════════════
//  config/auth.php  — ensure web guard uses sessions
//  (default Laravel config is fine, just confirming)
// ════════════════════════════════════════════════════════
/*
'guards' => [
    'web' => [
        'driver'   => 'session',
        'provider' => 'users',
    ],
    'sanctum' => [
        'driver'   => 'sanctum',
        'provider' => 'users',
    ],
],
*/
