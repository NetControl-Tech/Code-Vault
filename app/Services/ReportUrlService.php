<?php

namespace App\Services;

use App\Enums\BlocklistCategory;
use App\Enums\ReportStatus;
use App\Models\BlocklistDomain;
use App\Models\ReportedUrl;
use Illuminate\Support\Facades\DB;

class ReportUrlService
{
    protected UrlCheckService $urlCheckService;

    public function __construct(UrlCheckService $urlCheckService)
    {
        $this->urlCheckService = $urlCheckService;
    }

    /**
     * Submit a URL for review
     */
    public function submit(string $url, ?int $deviceId): array
    {
        $domain = $this->urlCheckService->extractDomain($url);

        if (!$domain) {
            return [
                'status' => 'error',
                'message' => 'Invalid URL format.',
                'code' => 400,
            ];
        }

        // Check if domain is already blocked
        $check = $this->urlCheckService->checkUrl($url);
        if ($check['status'] === 'blocked') {
            return [
                'status' => 'error',
                'message' => 'This URL is already blocked.',
                'code' => 400,
            ];
        }

        // Check if report already exists and is pending
        $existingReport = ReportedUrl::where('domain', $domain)
            ->where('status', ReportStatus::Pending->value)
            ->first();

        if ($existingReport) {
            return [
                'status' => 'success',
                'message' => 'This URL has already been reported and is pending review.',
                'report' => $existingReport,
                'code' => 200, // Returning 200 instead of 409 so app doesn't show error to user
            ];
        }

        $report = ReportedUrl::create([
            'url' => $url,
            'domain' => $domain,
            'device_id' => $deviceId,
            'status' => ReportStatus::Pending->value,
        ]);

        return [
            'status' => 'success',
            'message' => 'URL reported successfully. It will be reviewed soon.',
            'report' => $report,
            'code' => 201,
        ];
    }

    /**
     * Approve a reported URL and categorize it
     */
    public function approve(ReportedUrl $report, BlocklistCategory $category): array
    {
        if ($report->status !== ReportStatus::Pending) {
             return [
                 'status' => 'error',
                 'message' => 'Only pending reports can be approved.',
                 'code' => 400,
             ];
        }

        return DB::transaction(function () use ($report, $category) {
            $report->update([
                'status' => ReportStatus::Approved->value,
                'approved_category' => $category->value,
                'reviewed_at' => now(),
            ]);

            // Add to blocklist if it doesn't exist
            BlocklistDomain::firstOrCreate([
                'domain' => $report->domain,
                'category' => $category->value,
            ]);

            return [
                'status' => 'success',
                'message' => 'Report approved and domain added to blocklist.',
                'report' => $report,
                'code' => 200,
            ];
        });
    }

    /**
     * Reject a reported URL
     */
    public function reject(ReportedUrl $report): array
    {
         if ($report->status !== ReportStatus::Pending) {
             return [
                 'status' => 'error',
                 'message' => 'Only pending reports can be rejected.',
                 'code' => 400,
             ];
        }

        $report->update([
            'status' => ReportStatus::Rejected->value,
            'reviewed_at' => now(),
        ]);

        return [
            'status' => 'success',
            'message' => 'Report rejected.',
            'report' => $report,
            'code' => 200,
        ];
    }
}
