<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RedeemLicenseRequest;
use App\Services\RedeemService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class RedeemController extends Controller
{
    protected RedeemService $redeemService;

    public function __construct(RedeemService $redeemService)
    {
        $this->redeemService = $redeemService;
    }

    public function redeem(RedeemLicenseRequest $request)
    {
        $pinCode = $request->validated('pin_code');
        $deviceId = $request->validated('device_id');
        $ip = $request->ip();

        $ipKey = 'redeem_ip:' . $ip;
        $deviceKey = 'redeem_device:' . $deviceId;
        $maxAttempts = 3;
        $decaySecs = 15 * 60; // 15 mins limit

        // Prevent brute force
        if (RateLimiter::tooManyAttempts($ipKey, $maxAttempts) || RateLimiter::tooManyAttempts($deviceKey, $maxAttempts)) {
            Log::warning('Brute force redeem blocked', ['ip' => $ip, 'device_id' => $deviceId]);
            return response()->json([
                'status' => 'error',
                'message' => 'Too many attempts. Please try again later.'
            ], 429);
        }

        $result = $this->redeemService->redeem($pinCode, $deviceId);

        if ($result['status'] === 'error') {
            RateLimiter::hit($ipKey, $decaySecs);
            RateLimiter::hit($deviceKey, $decaySecs);

            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        RateLimiter::clear($ipKey);
        RateLimiter::clear($deviceKey);

        return response()->json([
            'status' => 'success',
            'device' => $result['device'],
            'license_code' => $result['license_code'],
            'token' => $result['token'],
        ], $result['code']);
    }
}
