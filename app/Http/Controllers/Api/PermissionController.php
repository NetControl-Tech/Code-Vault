<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Http\Resources\PermissionResource;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $permissions = Permission::all();
        return PermissionResource::collection($permissions);
    }

    /**
     * Store a newly created permission.
     */
    public function store(PermissionRequest $request)
    {
        $permission = Permission::create([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'guard_name' => 'api'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الصلاحية بنجاح',
            'data' => new PermissionResource($permission),
        ], 201);
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        return new PermissionResource($permission);
    }

    /**
     * Update the specified permission.
     */
    public function update(PermissionRequest $request, Permission $permission)
    {
        $permission->update([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الصلاحية بنجاح',
            'data' => new PermissionResource($permission),
        ]);
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission)
    {
        try {
            $permission->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الصلاحية بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في حذف الصلاحية: ' . $e->getMessage(),
            ], 422);
        }
    }
}
