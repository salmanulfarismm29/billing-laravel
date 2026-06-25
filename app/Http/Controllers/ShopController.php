<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;

use App\Http\Requests\{
    StoreShopRequest,
    UpdateShopRequest
};
use App\Models\Shop;
use App\Services\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(
        protected ShopService $shopService
    ) {}

    /**
     * Display a listing of shops.
     */
    public function getAllShops(): JsonResponse
    {
        // For admin to list all shops
        $shops = $this->shopService->getPaginated();
        return encryptResponse(200, 'success', 'Shops retrieved', $shops);
    }

    /**
     * Store a newly created shop.
     */
    public function addShop(StoreShopRequest $request): JsonResponse
    {
        $shop = $this->shopService->createShop($request->validated());
        return encryptResponse(201, 'success', 'Shop created successfully', $shop);
    }

    /**
     * Display the specified shop.
     */
    public function getShopInfo(Request $request): JsonResponse
    {
        $id = Shop::resolveHashedId($request->input('hash'));
        
        if (!$id) {
            return encryptResponse(404, 'error', 'Shop not found');
        }

        $shop = $this->shopService->getShopById($id);
        return encryptResponse(200, 'success', 'Shop retrieved', $shop);
    }

    /**
     * Update the specified shop.
     */
    public function updateShop(UpdateShopRequest $request): JsonResponse
    {
        $id = Shop::resolveHashedId($request->input('hash'));
        
        if (!$id) {
            return encryptResponse(404, 'error', 'Shop not found');
        }

        $shop = $this->shopService->getShopById($id);
        $shop = $this->shopService->updateShop($shop, $request->validated());
        
        return encryptResponse(200, 'success', 'Shop updated successfully', $shop);
    }

    /**
     * Toggle the active status of the shop.
     */
    public function updateShopStatus(Request $request): JsonResponse
    {
        // We'll just reuse the UpdateShopRequest authorization or apply a specific middleware.
        // For simplicity, we authorize manually or assume middleware protects it.
        if (auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $id = Shop::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'Shop not found');
        }

        $shop = $this->shopService->getShopById($id);
        $shop = $this->shopService->toggleActive($shop);
        
        return encryptResponse(200, 'success', 'Shop active status toggled', $shop);
    }

    /**
     * Remove the specified shop from storage.
     */
    public function deleteShop(Request $request): JsonResponse
    {
        if (auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $id = Shop::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'Shop not found');
        }

        $shop = $this->shopService->getShopById($id);
        $this->shopService->deleteShop($shop);

        return encryptResponse(200, 'success', 'Shop deleted successfully');
    }
}

