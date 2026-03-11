<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BlocklistCategory;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveReportRequest;
use App\Models\ReportedUrl;
use App\Services\ReportUrlService;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    protected ReportUrlService $reportService;

    public function __construct(ReportUrlService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $perPage = $request->input('per_page', 15);

        $query = ReportedUrl::with('device:id,device_id');

        if ($status && ReportStatus::tryFrom($status)) {
            $query->where('status', $status);
        }

        $reports = $query->latest()->paginate($perPage);

        return response()->json($reports);
    }

    public function approve(ApproveReportRequest $request, string $id)
    {
        $report = ReportedUrl::findOrFail($id);

        $category = BlocklistCategory::from($request->validated('category'));

        $result = $this->reportService->approve($report, $category);

        return response()->json($result, $result['code'] ?? 400);
    }

    public function reject(string $id)
    {
        $report = ReportedUrl::findOrFail($id);

        $result = $this->reportService->reject($report);

        return response()->json($result, $result['code'] ?? 400);
    }
}
