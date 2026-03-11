<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckUrlRequest;
use App\Http\Requests\Api\V1\ReportUrlRequest;
use App\Services\ReportUrlService;
use App\Services\UrlCheckService;
use Illuminate\Http\Request;

class ToolsController extends Controller
{
    protected UrlCheckService $urlCheckService;
    protected ReportUrlService $reportService;

    public function __construct(UrlCheckService $urlCheckService, ReportUrlService $reportService)
    {
        $this->urlCheckService = $urlCheckService;
        $this->reportService = $reportService;
    }

    public function checkUrl(CheckUrlRequest $request)
    {
        $validated = $request->validated();
        $result = $this->urlCheckService->checkUrl($validated['url']);
        
        return response()->json($result, $result['code'] ?? 400);
    }

    public function reportUrl(ReportUrlRequest $request)
    {
        $validated = $request->validated();
        
        /** @var \App\Models\Device $device */
        $device = $request->user();
        
        $result = $this->reportService->submit($validated['url'], $device?->id);

        return response()->json($result, $result['code'] ?? 400);
    }
}
