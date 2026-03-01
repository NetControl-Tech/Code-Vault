<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Device;
use App\Http\Requests\Api\StoreDeviceRequest;

class DeviceController extends Controller
{
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

    public function destroy(string $id)
    {
        $device = Device::findOrFail($id);

        $device->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Device deleted successfully'
        ]);
    }
}
