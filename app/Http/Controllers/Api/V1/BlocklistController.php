<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BlocklistCategory;
use App\Http\Controllers\Controller;
use App\Services\BlocklistService;
use Illuminate\Http\Request;

class BlocklistController extends Controller
{
    protected BlocklistService $blocklistService;

    public function __construct(BlocklistService $blocklistService)
    {
        $this->blocklistService = $blocklistService;
    }

    public function family(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        return response()->json($this->blocklistService->getByCategory(BlocklistCategory::Family->value, $perPage));
    }

    public function social(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        return response()->json($this->blocklistService->getByCategory(BlocklistCategory::Social->value, $perPage));
    }

    public function ads(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        return response()->json($this->blocklistService->getByCategory(BlocklistCategory::Ads->value, $perPage));
    }

    public function privacy(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        return response()->json($this->blocklistService->getByCategory(BlocklistCategory::Privacy->value, $perPage));
    }
}
