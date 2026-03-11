<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BlocklistCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkUploadRequest;
use App\Http\Requests\Admin\StoreDomainRequest;
use App\Http\Requests\Admin\UpdateDomainRequest;
use App\Models\BlocklistDomain;
use App\Services\BlocklistService;
use Illuminate\Http\Request;

class AdminBlocklistController extends Controller
{
    protected BlocklistService $blocklistService;

    public function __construct(BlocklistService $blocklistService)
    {
        $this->blocklistService = $blocklistService;
    }

    public function index(Request $request)
    {
        $category = $request->query('category');
        
        if (!$category || !BlocklistCategory::tryFrom($category)) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'Valid category is required.',
             ], 400);
        }

        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $domains = $this->blocklistService->getByCategory($category, $perPage, $search);

        return response()->json($domains);
    }

    public function store(StoreDomainRequest $request)
    {
        $validated = $request->validated();

        $domain = BlocklistDomain::create([
            'domain' => strtolower($validated['domain']),
            'category' => $validated['category'],
        ]);

        return response()->json([
             'status' => 'success',
             'message' => 'Domain added successfully.',
             'domain' => $domain,
        ], 201);
    }

    public function bulkUpload(BulkUploadRequest $request)
    {
        $validated = $request->validated();
        
        $result = $this->blocklistService->bulkImport($request->file('file'), $validated['category']);

        if ($result['status'] === 'error') {
             return response()->json($result, 400);
        }

        return response()->json($result, 201);
    }

    public function update(UpdateDomainRequest $request, string $id)
    {
        $domain = BlocklistDomain::findOrFail($id);
        $validated = $request->validated();

        $domain->update([
             'domain' => strtolower($validated['domain']),
             'category' => $validated['category'],
        ]);

        return response()->json([
             'status' => 'success',
             'message' => 'Domain updated successfully.',
             'domain' => $domain,
        ]);
    }

    public function destroy(string $id)
    {
        $domain = BlocklistDomain::findOrFail($id);
        $domain->delete();

        return response()->json([
             'status' => 'success',
             'message' => 'Domain deleted successfully.',
        ]);
    }
}
