<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !($request->user() instanceof \App\Models\Device)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device token required.',
                'code' => 403,
            ], 403);
        }

        return $next($request);
    }
}
