<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\Setting;
use App\Http\Requests\SettingRequest;
use App\Http\Resources\SettingResource;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $settings = Setting::paginate(20);

        return SettingResource::collection($settings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SettingRequest $request): JsonResponse
    {
        $setting = Setting::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الإعداد بنجاح',
            'data' => new SettingResource($setting),
        ], 201);
    }

    /**
     * Show the specified resource.
     */
    public function show($id): SettingResource
    {
        $setting = Setting::findOrFail($id);

        return new SettingResource($setting);
    }



    public function update(SettingRequest $request): JsonResponse
    {
        $settings = $request->input('settings');
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الإعدادات بنجاح',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $setting = Setting::findOrFail($id);
            $setting->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الإعداد بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في حذف الإعداد: ' . $e->getMessage(),
            ], 422);
        }
    }
}
