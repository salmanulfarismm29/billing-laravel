<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Display a dashboard summary of analytics.
     */
    public function getDashboardAnalytics(): JsonResponse
    {
        $shopId = app()->bound(Shop::class) ? app(Shop::class)->id : null;

        if (!$shopId) {
            return encryptResponse(400, 'error', 'Shop context is required for analytics');
        }

        $stats = $this->analyticsService->getDashboardStats($shopId);

        return encryptResponse(200, 'success', 'Dashboard analytics retrieved', $stats);
    }
}

