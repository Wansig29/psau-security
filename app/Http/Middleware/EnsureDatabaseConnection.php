<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnsureDatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * Railway's TCP proxy aggressively drops idle MySQL connections after ~60s.
     * This middleware pings the connection before every web request and
     * reconnects if it has been severed, preventing SQLSTATE[HY000]: 2006
     * "MySQL server has gone away" errors on any route.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            DB::connection()->getPdo()->query('SELECT 1');
        } catch (\Exception $e) {
            Log::info('DB connection lost — reconnecting. Reason: ' . $e->getMessage());
            try {
                DB::reconnect();
            } catch (\Exception $reconnectError) {
                Log::error('DB reconnect failed: ' . $reconnectError->getMessage());
            }
        }

        return $next($request);
    }
}
