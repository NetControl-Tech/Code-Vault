<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Get aggregated statistics for all counseling modules.
     */
    public function index(): JsonResponse
    {
        $letterService = app(\App\Services\LetterService::class);
        $stats = $letterService->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get subjects count per category.
     * 
     * Returns count of distinct subjects linked to each category via letters.
     */
    public function subjectsPerCategory(): JsonResponse
    {
        $data = $this->dashboardService->getSubjectsCountByCategory();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get calendar events for letters.
     */
    public function calendar(Request $request): JsonResponse
    {
        $start = $request->input('start', now()->startOfMonth()->toDateString());
        $end = $request->input('end', now()->endOfMonth()->toDateString());

        $letters = \App\Models\Letter::with(['category', 'subject'])
            ->whereBetween('date', [$start, $end])
            ->get();

        $events = $letters->map(function ($letter) {
            return [
                'id' => $letter->id,
                'title' => "{$letter->category->title}: {$letter->subject->title}",
                'start' => $letter->date->toDateString(),
                'type' => 'letter',
                'className' => 'bg-indigo-500',
                'extendedProps' => [
                    'letter_number' => $letter->letter_number,
                    'category' => $letter->category->title,
                    'subject' => $letter->subject->title
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }
    /**
     * Get total count of non-cooperative letters.
     */
    public function nonCooperativeCount(): JsonResponse
    {
        $letterService = app(\App\Services\LetterService::class);
        $count = $letterService->getNonCooperativeCount();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
        ]);
    }

    /**
     * Get non-cooperative letters count by category.
     */
    public function nonCooperativeByCategory(): JsonResponse
    {
        $letterService = app(\App\Services\LetterService::class);
        $data = $letterService->getNonCooperativeByCategory();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
