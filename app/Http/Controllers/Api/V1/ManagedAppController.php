<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ManagedAppService;

class ManagedAppController extends Controller
{
    protected ManagedAppService $managedAppService;

    public function __construct(ManagedAppService $managedAppService)
    {
        $this->managedAppService = $managedAppService;
    }

    /**
     * Return the global app access list for the device to apply per installed app.
     * Each entry: internet_block true = hard block via VPN, false = allow + warn.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'apps' => $this->managedAppService->getAppsForDevice(),
        ]);
    }
}
