<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManagedAppRequest;
use App\Http\Requests\Admin\UpdateManagedAppRequest;
use App\Models\ManagedApp;
use App\Services\ManagedAppService;
use Illuminate\Http\Request;

class AdminManagedAppController extends Controller
{
    protected ManagedAppService $managedAppService;

    public function __construct(ManagedAppService $managedAppService)
    {
        $this->managedAppService = $managedAppService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        return response()->json($this->managedAppService->getPaginated($perPage, $search));
    }

    public function store(StoreManagedAppRequest $request)
    {
        $app = ManagedApp::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'App added successfully.',
            'app' => $app,
        ], 201);
    }

    public function update(UpdateManagedAppRequest $request, string $id)
    {
        $app = ManagedApp::findOrFail($id);
        $app->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'App updated successfully.',
            'app' => $app,
        ]);
    }

    public function destroy(string $id)
    {
        $app = ManagedApp::findOrFail($id);
        $app->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'App deleted successfully.',
        ]);
    }
}
