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

    /**
     * Return the full list of ad/tracker domains for DNS-level ad blocking.
     * Consumed in full by the mobile app on launch and periodic refresh.
     */
    public function adBlockList()
    {
        return response()->json([
            'status'  => true,
            'domains' => $this->blocklistService->getAdBlockDomains(),
        ]);
    }

    /**
     * Return the full list of family-safety domains (adult content, gambling,
     * age-restricted sites). Kept separate from the ad-block list so the
     * parental-control filter can be toggled independently in the app.
     */
    public function familySafetyList()
    {
        return response()->json([
            'status'  => true,
            'domains' => $this->blocklistService->getFamilySafetyDomains(),
        ]);
    }
}
