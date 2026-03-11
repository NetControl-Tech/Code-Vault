<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ActivateDeviceRequest;
use App\Http\Requests\Api\StoreDeviceRequest;
use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class DeviceController extends Controller
{
    protected DeviceService $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    // ──────────────────────────────────────────────
    //  Device CRUD (Admin)
    // ──────────────────────────────────────────────

    /**
     * List all devices (paginated, filterable).
     */
    public function index(Request $request)
    {
        $query = Device::query();

        if ($request->has('device_id')) {
            $query->where('device_id', 'like', '%' . $request->input('device_id') . '%');
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($query->paginate(15));
    }

    /**
     * Store a new device.
     */
    public function store(StoreDeviceRequest $request)
    {
        $device = Device::create([
            'device_id' => $request->validated('device_id'),
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'device' => $device,
        ], 201);
    }

    /**
     * Delete a device.
     */
    public function destroy(string $id)
    {
        $device = Device::findOrFail($id);
        $device->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Device deleted successfully'
        ]);
    }

    // ──────────────────────────────────────────────
    //  Device Auth (NetControl V1)
    // ──────────────────────────────────────────────

    /**
     * Activate a device using a license PIN code.
     * Rate limited: 3 attempts / 15 min per IP and device.
     */
    public function activate(ActivateDeviceRequest $request)
    {
        $validated = $request->validated();
        $ip = $request->ip();
        $deviceId = $validated['device_id'];

        $ipKey = 'activate_ip:' . $ip;
        $deviceKey = 'activate_device:' . $deviceId;
        $maxAttempts = 3;
        $decaySecs = 15 * 60; // 15 minutes

        // Prevent brute force
        if (RateLimiter::tooManyAttempts($ipKey, $maxAttempts) || RateLimiter::tooManyAttempts($deviceKey, $maxAttempts)) {
            Log::warning('Brute force activate blocked', ['ip' => $ip, 'device_id' => $deviceId]);
            return response()->json([
                'status' => 'error',
                'message' => 'Too many attempts. Please try again later.'
            ], 429);
        }

        $result = $this->deviceService->activate($validated['pin_code'], $deviceId);

        if ($result['status'] === 'error') {
            RateLimiter::hit($ipKey, $decaySecs);
            RateLimiter::hit($deviceKey, $decaySecs);
        } else {
            RateLimiter::clear($ipKey);
            RateLimiter::clear($deviceKey);
        }

        return response()->json($result, $result['code'] ?? 400);
    }

    /**
     * Check current subscription status.
     */
    public function status(Request $request)
    {
        /** @var Device $device */
        $device = $request->user();

        $result = $this->deviceService->status($device);

        return response()->json($result, $result['code'] ?? 400);
    }

    /**
     * Unlink device from its license code and revoke all tokens.
     */
    public function unlink(Request $request)
    {
        /** @var Device $device */
        $device = $request->user();

        $result = $this->deviceService->unlink($device);

        return response()->json($result, $result['code'] ?? 400);
    }
}
