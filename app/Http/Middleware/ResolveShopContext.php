<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Shop;
use Vinkla\Hashids\Facades\Hashids;

class ResolveShopContext
{
    /**
     * Handle an incoming request and bind Shop to container.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $headerHash = $request->header('X-Shop-ID');
        
        if (!$headerHash) {
            // Check if user is admin/manager who might not need shop context globally
            // But for billing/products, they might send it or it comes from their assigned shop
            $user = $request->user();
            if ($user && $user->shop_id) {
                // Fallback to user's assigned shop
                app()->instance(Shop::class, $user->shop);
                return $next($request);
            }
            
            // In API context where this middleware is mandatory, error out
            // return encryptResponse(400, 'error', 'X-Shop-ID header is required');
            return $next($request);
        }

        $shopId = Hashids::decode($headerHash)[0] ?? null;

        if (!$shopId) {
            return encryptResponse(400, 'error', 'Invalid Shop ID');
        }

        $shop = Shop::active()->find($shopId);

        if (!$shop) {
            return encryptResponse(404, 'error', 'Shop not found or inactive');
        }

        // Bind for dependency injection globally
        app()->instance(Shop::class, $shop);

        return $next($request);
    }
}
