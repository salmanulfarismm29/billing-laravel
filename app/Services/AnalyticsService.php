<?php

namespace App\Services;

use App\Models\Bill;
use Illuminate\Support\Carbon;

class AnalyticsService
{
    /**
     * Get dashboard analytics for a specific shop.
     */
    public function getDashboardStats(int $shopId): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $billsQuery = Bill::where('shop_id', $shopId);

        $totalRevenueToday = (clone $billsQuery)->whereDate('created_at', $today)->sum('total');
        $totalRevenueMonth = (clone $billsQuery)->where('created_at', '>=', $startOfMonth)->sum('total');
        
        $totalBillsToday = (clone $billsQuery)->whereDate('created_at', $today)->count();
        $totalBillsMonth = (clone $billsQuery)->where('created_at', '>=', $startOfMonth)->count();

        $recentBills = (clone $billsQuery)
            ->with(['cashier:id,name', 'items:id,bill_id,product_id,quantity,price_at_time_of_sale', 'items.product:id,name'])
            ->latest()
            ->take(5)
            ->get();

        return [
            'revenue' => [
                'today' => round($totalRevenueToday, 2),
                'month' => round($totalRevenueMonth, 2),
            ],
            'bills_count' => [
                'today' => $totalBillsToday,
                'month' => $totalBillsMonth,
            ],
            'recent_bills' => $recentBills,
        ];
    }
}
