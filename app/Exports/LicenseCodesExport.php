<?php

namespace App\Exports;

use App\Models\LicenseCode;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LicenseCodesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return LicenseCode::query()->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Serial',
            'Status',
            'Duration (Days)',
            'Device ID',
            'Activated At',
            'Redeemed At',
            'Expires At',
            'Created At',
        ];
    }

    public function map($license): array
    {
        return [
            $license->serial,
            $license->status->value,
            $license->duration_days,
            $license->device->device_id ?? 'N/A',
            $license->activated_at ? $license->activated_at->format('Y-m-d H:i:s') : '',
            $license->redeemed_at ? $license->redeemed_at->format('Y-m-d H:i:s') : '',
            $license->expires_at ? $license->expires_at->format('Y-m-d H:i:s') : '',
            $license->created_at ? $license->created_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
