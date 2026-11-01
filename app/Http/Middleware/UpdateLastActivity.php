<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            // Update only if last_activity_at is null or older than 1 minute to avoid excessive DB writes.
            if (! $user->last_activity_at || $user->last_activity_at->diffInMinutes(now()) >= 1) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['last_activity_at' => now()]);
            }
        }

        return $next($request);
    }
}
