<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * Get paginated products contextually filtered by shop.
     */
    public function getPaginated(int $shopId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::latest();
        
        if ($shopId) {
            $query->where('shop_id', $shopId)->orWhereNull('shop_id');
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new product.
     */
    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Get a product by ID.
     */
    public function getProductById(int $id): Product
    {
        return Product::findOrFail($id);
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);
        return $product;
    }

    /**
     * Toggle the active status of a product.
     */
    public function toggleActive(Product $product): Product
    {
        $product->update(['is_active' => !$product->is_active]);
        return $product;
    }

    /**
     * Soft delete a product.
     */
    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Get distinct price groups (price → count) for active products in a shop.
     *
     * The $selectedPrices array is used to bubble the already-configured prices
     * to the top of the list, mirroring how the POS admin config UI must behave:
     * the currently selected shortcuts appear first so they are immediately visible.
     */
    public function getPriceGroups(int $shopId, array $selectedPrices = []): array
    {
        $rows = Product::active()
            ->where(function ($query) use ($shopId) {
                // Include both shop-specific and global products
                $query->where('shop_id', $shopId)->orWhereNull('shop_id');
            })
            ->selectRaw('price, COUNT(*) as product_count')
            ->groupBy('price')
            ->orderBy('price')
            ->get();

        // Cast selectedPrices to floats so we can compare against the decimal DB values
        $selectedFloats = array_map('floatval', $selectedPrices);

        // Partition: selected prices float to the top, the rest follow sorted ascending
        $selected    = $rows->filter(fn($row) => in_array((float) $row->price, $selectedFloats, true));
        $unselected  = $rows->reject(fn($row) => in_array((float) $row->price, $selectedFloats, true));

        // Maintain the admin's chosen order for the selected group
        $orderedSelected = collect($selectedFloats)
            ->map(fn($price) => $selected->firstWhere(fn($row) => (float) $row->price === $price))
            ->filter();

        return $orderedSelected->merge($unselected)
            ->map(fn($row) => [
                'price'        => (float) $row->price,
                'productCount' => (int) $row->product_count,
                'isSelected'   => in_array((float) $row->price, $selectedFloats, true),
            ])
            ->values()
            ->all();
    }

    /**
     * Return all active products in a shop that match an exact price point.
     *
     * Called when a cashier taps a Quick Action Button (e.g. "₹15") on the POS grid.
     */
    public function getProductsByPrice(int $shopId, float $price): \Illuminate\Support\Collection
    {
        return Product::active()
            ->where(function ($query) use ($shopId) {
                $query->where('shop_id', $shopId)->orWhereNull('shop_id');
            })
            ->where('price', $price)
            ->get();
    }
}

