<?php

namespace App\Services;

use App\Models\{
    Bill,
    Product,
    Shop
};

use Illuminate\{
    Pagination\LengthAwarePaginator,
    Support\Facades\DB
};
use Vinkla\Hashids\Facades\Hashids;

class BillService
{
    /**
     * Get paginated bills contextually filtered by shop.
     */
    public function getPaginated(int $shopId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Bill::with(['items', 'cashier'])->latest();
        
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new bill with its items.
     */
    public function createBill(array $data, int $shopId, int $cashierId): Bill
    {
        return DB::transaction(function () use ($data, $shopId, $cashierId) {
            $total = 0;
            $itemsData = [];

            // Pre-load all products to prevent N+1
            $productIds = collect($data['items'])->pluck('product_id')->unique()->toArray();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            // Resolve and Validate Products
            foreach ($data['items'] as $item) {
                $productId = $item['product_id'];
                
                if (!$products->has($productId)) {
                    abort(400, "Product not found: {$productId}");
                }

                $product = $products->get($productId);

                if (!$product->is_active) {
                    abort(400, "Product is inactive: {$product->name}");
                }

                // Verify product belongs to current shop context or global
                if ($product->shop_id !== null && $product->shop_id !== $shopId) {
                    abort(400, "Product does not belong to the active shop: {$product->name}");
                }

                $quantity = $item['quantity'];
                $subtotal = $product->price * $quantity;
                $total += $subtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price_at_time_of_sale' => $product->price,
                ];
            }

            // Create Bill
            $bill = Bill::create([
                'shop_id' => $shopId,
                'cashier_id' => $cashierId,
                'total' => $total,
                'payment_method' => $data['payment_method'],
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'qr_code' => null, // To be generated after creation if needed
            ]);

            // Create Bill Items
            foreach ($itemsData as $itemData) {
                $bill->items()->create($itemData);
            }

            // Generate receipt or QR code URL here (optional based on future specs)
            $bill->update([
                'qr_code' => url('/receipt/' . $bill->hashed_id),
            ]);

            return $bill->load('items');
        });
    }

    /**
     * Get a bill by ID.
     */
    public function getBillById(int $id, int $shopId = null): Bill
    {
        $query = Bill::with(['items', 'cashier']);
        
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        return $query->findOrFail($id);
    }

    /**
     * Soft delete a bill (Admin only).
     */
    public function deleteBill(Bill $bill): bool
    {
        return $bill->delete();
    }
}
