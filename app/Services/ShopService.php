<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Pagination\LengthAwarePaginator;

class ShopService
{
    /**
     * Get paginated shops.
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Shop::latest()->paginate($perPage);
    }

    /**
     * Get all active shops.
     */
    public function getActiveShops()
    {
        return Shop::active()->latest()->get();
    }

    /**
     * Create a new shop.
     */
    public function createShop(array $data): Shop
    {
        return Shop::create($data);
    }

    /**
     * Get a shop by ID.
     */
    public function getShopById(int $id): Shop
    {
        return Shop::findOrFail($id);
    }

    /**
     * Update an existing shop.
     */
    public function updateShop(Shop $shop, array $data): Shop
    {
        $shop->update($data);
        return $shop;
    }

    /**
     * Toggle the active status of a shop.
     */
    public function toggleActive(Shop $shop): Shop
    {
        $shop->update(['is_active' => !$shop->is_active]);
        return $shop;
    }

    /**
     * Soft delete a shop.
     */
    public function deleteShop(Shop $shop): bool
    {
        return $shop->delete();
    }
}
