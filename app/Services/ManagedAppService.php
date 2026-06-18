<?php

namespace App\Services;

use App\Models\ManagedApp;

class ManagedAppService
{
    /**
     * Return the full app list for a device as a flat array of
     * {package_name, internet_block}. Sorted, unpaginated — the app consumes
     * the whole list and applies the policy per installed app.
     *
     * @return array<int, array{package_name: string, internet_block: bool}>
     */
    public function getAppsForDevice(): array
    {
        return ManagedApp::orderBy('package_name')
            ->get(['package_name', 'internet_block'])
            ->map(fn (ManagedApp $app) => [
                'package_name' => $app->package_name,
                'internet_block' => $app->internet_block,
            ])
            ->all();
    }

    /**
     * Get paginated managed apps for the admin panel, optionally filtered by package name.
     */
    public function getPaginated(int $perPage = 15, ?string $search = null)
    {
        $query = ManagedApp::query();

        if ($search) {
            $query->where('package_name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }
}
