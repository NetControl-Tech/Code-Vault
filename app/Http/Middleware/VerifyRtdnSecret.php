<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRtdnSecret
{
    /**
     * Authenticate the Google Play RTDN webhook with a shared secret.
     *
     * The Pub/Sub push subscription presents the secret via a `?token=` query
     * param (configured on the push endpoint URL) or an `X-RTDN-Token` header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('googleplay.rtdn_secret');
        $provided = $request->query('token') ?? $request->header('X-RTDN-Token');

        if (!$expected || !is_string($provided) || !hash_equals($expected, $provided)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
