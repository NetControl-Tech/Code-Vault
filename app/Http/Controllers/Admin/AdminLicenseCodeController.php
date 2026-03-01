<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivateLicenseCodesRequest;
use App\Http\Requests\Admin\DestroyLicenseCodesRangeRequest;
use App\Http\Requests\Admin\GenerateLicenseCodesRequest;
use App\Http\Requests\Admin\RenewLicenseCodeRequest;
use App\Exports\GeneratedLicenseCodesExport;
use App\Exports\LicenseCodesExport;
use App\Enums\LicenseCodeStatus;
use App\Models\LicenseCode;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class AdminLicenseCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = LicenseCode::with('device:id,device_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial', 'like', "%{$search}%")
                    ->orWhere('pin_hash', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $codes = $query->orderBy('id', 'desc')->paginate($perPage);

        // Append device_id from the device relationship
        $codes->getCollection()->transform(function ($code) {
            $code->device_hardware_id = $code->device?->device_id;
            unset($code->device);
            return $code;
        });

        return response()->json($codes);
    }

    public function destroyRange(DestroyLicenseCodesRangeRequest $request)
    {
        $from = (int) $request->validated('from_serial');
        $to = (int) $request->validated('to_serial');

        $existingCount = LicenseCode::whereBetween('serial', [$from, $to])->count();

        if ($existingCount === 0) {
            return response()->json([
                'status' => 'error',

                'message' => 'لا توجد أكواد في هذا النطاق',
            ], 404);
        }

        $deletedCount = LicenseCode::whereBetween('serial', [$from, $to])->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف ' . $deletedCount . ' كود بنجاح',
            'deleted_count' => $deletedCount
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(new LicenseCodesExport, 'license_codes_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    public function generate(GenerateLicenseCodesRequest $request)
    {
        $count = (int) $request->validated('count');

        $duration = 30;

        $maxSerial = LicenseCode::max('serial');
        $startingSerial = ($maxSerial ?? 9999) + 1;
        $currentSerial = $startingSerial;

        $insertData = [];
        $exportData = [];

        for ($i = 0; $i < $count; $i++) {
            $pin = strtoupper(Str::random(12));
            $serial = $currentSerial++;

            $insertData[] = [
                'serial' => $serial,
                'pin_hash' => hash('sha256', $pin),
                'status' => LicenseCodeStatus::Inactive->value,
                'duration_days' => $duration,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $exportData[] = [$serial, $pin];
        }

        foreach (array_chunk($insertData, 1000) as $chunk) {
            LicenseCode::insert($chunk);
        }

        return Excel::download(
            new GeneratedLicenseCodesExport($exportData),
            'generated_license_codes_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    public function activateRange(ActivateLicenseCodesRequest $request)
    {
        $from = (int) $request->validated('from_serial');
        $to = (int) $request->validated('to_serial');

        $existingCount = LicenseCode::whereBetween('serial', [$from, $to])
            ->where('status', LicenseCodeStatus::Inactive->value)
            ->count();

        if ($existingCount === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا توجد أكواد غير مفعلة في هذا النطاق',
            ], 404);
        }

        $updatedCount = LicenseCode::whereBetween('serial', [$from, $to])
            ->where('status', LicenseCodeStatus::Inactive->value)
            ->update([
                'status' => LicenseCodeStatus::Active->value,
                'activated_at' => now()
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تفعيل ' . $updatedCount . ' كود بنجاح',
            'activated_count' => $updatedCount
        ]);
    }

    public function renew(RenewLicenseCodeRequest $request, string $code)
    {
        $validated = $request->validated();

        $service = new \App\Services\LicenseCodeRenewalService();
        $result = $service->renew($code, $validated['duration_days']);

        if ($result['status'] === 'error') {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'status' => 'success',
            'license_code' => $result['license_code']
        ], $result['code']);
    }
}
