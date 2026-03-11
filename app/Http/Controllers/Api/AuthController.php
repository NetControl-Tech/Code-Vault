<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Authentication Controller
 */
class AuthController extends Controller
{
    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request, TwoFactorAuthService $twoFactorService): JsonResponse
    {
        $throttleKey = $this->generateThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
            ], 429);
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        // Check credentials
        if (!$user || !Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($throttleKey, 60);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }


        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is disabled. Please contact the administrator',
            ], 403);
        }

        RateLimiter::clear($throttleKey);

        $twoFactorService->generateAndSendCode($user);

        // Create temporary token for 2FA validation
        $token = $user->createToken('2fa-token', ['2fa'], now()->addMinutes(10))->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني',
            'requires_2fa' => true,
            'data' => [
                'temp_token' => $token,
            ],
        ]);
    }

    /**
     * Verify 2FA code.
     */
    public function verify2FA(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        // Check if token has 2fa ability
        if (!$user->currentAccessToken()->can('2fa')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token type',
            ], 403);
        }

        // Check code expiration
        if (!$user->two_factor_expires_at || $user->two_factor_expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'انتهت صلاحية الرمز، يرجى طلب رمز جديد',
            ], 400);
        }

        // Check code validity
        if (!Hash::check($request->code, $user->two_factor_code)) {
            return response()->json([
                'success' => false,
                'message' => 'رمز التحقق غير صحيح',
            ], 400);
        }

        // Clear 2FA fields
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        // Delete temporary token
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $currentToken = $user->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }

        // Issue full token
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Resend 2FA code.
     */
    public function resend2FA(Request $request, TwoFactorAuthService $twoFactorService): JsonResponse
    {
        $user = $request->user();

        if (!$user->currentAccessToken()->can('2fa')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token type',
            ], 403);
        }

        $twoFactorService->generateAndSendCode($user);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رمز التحقق مجدداً إلى بريدك الإلكتروني',
        ]);
    }

    /**
     * Cancel 2FA process.
     */
    public function cancel2FA(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->currentAccessToken()->can('2fa')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token type',
            ], 403);
        }

        // Clear 2FA fields
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        // Delete temporary token
        /** @var \Laravel\Sanctum\PersonalAccessToken $currentToken */
        $currentToken = $user->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }

        return response()->json([
            'success' => true,
            'message' => '2FA cancelled successfully',
        ]);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get current authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    protected function generateThrottleKey(LoginRequest $request): string
    {
        return strtolower(trim($request->email)) . '|' . $request->ip();
    }
}
