<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * User Management Controller
 * Admin only
 */
class UserController extends Controller
{

    use AuthorizesRequests;
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles');

        if (Auth::id() !== 1) {
            $query->where('id', '!=', 1);
        }

        $users = $query->filter($request->only(['search', 'role', 'is_active']))
            ->sorted($request->input('sort_by', 'created_at'), $request->input('sort_order', 'desc'))
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'role' => $user->roles->first()?->name,
                    'created_at' => $user->created_at->format('Y-m-d H:i'),
                ];
            }),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Store a newly created user.
     * إنشاء مستخدم جديد
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المستخدم بنجاح',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'role' => $validated['role'],
            ],
        ], 201);
    }

    /**
     * Display the specified user.
     * عرض مستخدم محدد
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'role' => $user->roles->first()?->name,
                'created_at' => $user->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Update the specified user.
     * تحديث مستخدم محدد
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if ($user->id === 1 && Auth::user()->id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل بيانات المدير العام',
            ], 403);
        }

        $validated = $request->validated();

        // Update basic info
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        if (isset($validated['is_active']) && $user->id !== 1) {
            $user->is_active = $validated['is_active'];
        }

        $user->save();

        // Update role if provided
        if (isset($validated['role']) && $user->id !== 1) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المستخدم بنجاح',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'role' => $user->roles->first()?->name,
            ],
        ]);
    }

    /**
     * Toggle user active status.
     * تغيير حالة تفعيل المستخدم
     */
    public function toggleActive(User $user): JsonResponse
    {
        $this->authorize('update', $user);

        if ($user->id === 1) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعطيـل حساب المدير العام',
            ], 403);
        }

        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك تعطيل حسابك الخاص',
            ], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'تم تفعيل المستخدم' : 'تم تعطيل المستخدم',
            'data' => [
                'id' => $user->id,
                'is_active' => $user->is_active,
            ],
        ]);
    }
    /**
     * Remove the specified user.
     * حذف مستخدم
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        if ($user->id === 1) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف حساب المدير العام',
            ], 403);
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك حذف حسابك الخاص',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المستخدم بنجاح',
        ]);
    }
}
