<?php

namespace App\Http\Controllers;

use App\Models\ChildCounseling;
use App\Models\MaternalCounseling;
use App\Models\PremaritalCounseling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Get statistics for premarital counseling.
     */
    public function premarital(Request $request): JsonResponse
    {
        return $this->getStats(PremaritalCounseling::query(), $request);
    }

    /**
     * Get statistics for maternal counseling.
     */
    public function maternal(Request $request): JsonResponse
    {
        return $this->getStats(MaternalCounseling::query(), $request);
    }

    /**
     * Get statistics for child counseling.
     */
    public function child(Request $request): JsonResponse
    {
        return $this->getStats(ChildCounseling::query(), $request);
    }

    /**
     * Helper to get stats grouped by governorate, district, and health facility.
     */
    private function getStats($query, Request $request): JsonResponse
    {
        // Clone query for each aggregation to avoid side effects
        $baseQuery = $query->clone();

        // Apply filters if needed (optional)
        if ($request->filled('date_from')) {
            $baseQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $baseQuery->whereDate('created_at', '<=', $request->date_to);
        }

        // By Governorate
        $byGovernorate = $baseQuery->clone()
            ->join('governorates', 'governorates.id', '=', 'governorate_id')
            ->select('governorates.id', 'governorates.name', DB::raw('count(*) as count'))
            ->groupBy('governorates.id', 'governorates.name')
            ->get();

        // By District
        $byDistrict = $baseQuery->clone()
            ->join('districts', 'districts.id', '=', 'district_id')
            ->select('districts.id', 'districts.name', DB::raw('count(*) as count'))
            ->groupBy('districts.id', 'districts.name')
            ->get();

        // By Health Facility
        $byHealthFacility = $baseQuery->clone()
            ->join('health_facilities', 'health_facilities.id', '=', 'health_facility_id')
            ->select('health_facilities.id', 'health_facilities.name', DB::raw('count(*) as count'))
            ->groupBy('health_facilities.id', 'health_facilities.name')
            ->orderByDesc('count')
            ->limit(20) // Limit to top 20 to avoid huge lists in charts
            ->get();

        return response()->json([
            'by_governorate' => $byGovernorate,
            'by_district' => $byDistrict,
            'by_health_facility' => $byHealthFacility,
        ]);
    }
}
