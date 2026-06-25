<?php

namespace App\Http\Controllers;

use App\Models\{
    Product,
    Shop
};

use App\Enums\UserRole;

use App\Http\Requests\{
    GetProductsByPriceRequest,
    StoreProductRequest,
    UpdateProductRequest
};
use App\Services\{
    ProductService,
    SettingsService
};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService  $productService,
        protected SettingsService $settingsService,
    ) {}

    /**
     * Get the resolved shop ID context from the middleware binding.
     */
    protected function getContextShopId(): ?int
    {
        return app()->bound(Shop::class) ? app(Shop::class)->id : null;
    }

    /**
     * Display a listing of products.
     */
    public function getAllProducts(): JsonResponse
    {
        $shopId = $this->getContextShopId();

        $products = $this->productService->getPaginated($shopId);
        return encryptResponse(200, 'success', 'Products retrieved', $products);
    }

    /**
     * Store a newly created product.
     */
    public function addProduct(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // If shop_id is not provided but we have a context shop, use it
        if (!isset($data['shop_id']) && $shopId = $this->getContextShopId()) {
            $data['shop_id'] = $shopId;
        }

        $product = $this->productService->createProduct($data);
        return encryptResponse(201, 'success', 'Product created successfully', $product);
    }

    /**
     * Display the specified product.
     */
    public function getProductInfo(Request $request): JsonResponse
    {
        $id = Product::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'Product not found');
        }

        $product = $this->productService->getProductById($id);
        
        // Ensure the product belongs to the requested shop context
        if ($product->shop_id && $shopId = $this->getContextShopId()) {
            if ($product->shop_id !== $shopId) {
                return encryptResponse(403, 'error', 'Product does not belong to the active shop');
            }
        }

        return encryptResponse(200, 'success', 'Product retrieved', $product);
    }

    /**
     * Update the specified product.
     */
    public function updateProduct(UpdateProductRequest $request): JsonResponse
    {
        $id = Product::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'Product not found');
        }

        $product = clone $this->productService->getProductById($id);
        
        // Verify shop ownership
        if ($product->shop_id && $shopId = $this->getContextShopId()) {
            if ($product->shop_id !== $shopId && auth('api')->user()->role !== UserRole::ADMIN) {
                return encryptResponse(403, 'error', 'Unauthorized to modify this product');
            }
        }

        $product = $this->productService->updateProduct($product, $request->validated());
        
        return encryptResponse(200, 'success', 'Product updated successfully', $product);
    }

    /**
     * Toggle the active status of the product.
     */
    public function updateProductStatus(Request $request): JsonResponse
    {
        // Require ADMIN for simplicity, or MANAGER
        if (auth('api')->user()->role === UserRole::CASHIER) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $id = Product::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'Product not found');
        }

        $product = $this->productService->getProductById($id);
        
        // Verify shop ownership
        if ($product->shop_id && $shopId = $this->getContextShopId()) {
            if ($product->shop_id !== $shopId && auth('api')->user()->role !== UserRole::ADMIN) {
                return encryptResponse(403, 'error', 'Unauthorized');
            }
        }

        $product = $this->productService->toggleActive($product);
        
        return encryptResponse(200, 'success', 'Product active status toggled', $product);
    }

    /**
     * Remove the specified product from storage.
     */
    public function deleteProduct(Request $request): JsonResponse
    {
        if (auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $id = Product::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'Product not found');
        }

        $product = $this->productService->getProductById($id);
        $this->productService->deleteProduct($product);

        return encryptResponse(200, 'success', 'Product deleted successfully');
    }

    /**
     * GET /api/v1/product/getpricegroups
     *
     * Returns all distinct price points for active products in this shop,
     * already sorted with the admin's selected shortcut prices at the top.
     */
    public function getPriceGroups(): JsonResponse
    {
        $shopId = $this->getContextShopId();
        if (!$shopId) {
            return encryptResponse(400, 'error', 'Shop context is required');
        }

        // Fetch which prices the admin has already configured as shortcuts so
        // we know which ones to float to the top of the response list.
        $selectedPrices = $this->settingsService->getBillingCalculatorPrices($shopId);

        $groups = $this->productService->getPriceGroups($shopId, $selectedPrices);

        return encryptResponse(200, 'success', 'Price groups retrieved', $groups);
    }

    /**
     * POST /api/v1/product/getproductsbyprice
     *
     * Cashier taps a Quick-Action price button → returns all active products
     * at that exact price point so the cashier can pick which one to add.
     */
    public function getProductsByPrice(GetProductsByPriceRequest $request): JsonResponse
    {
        $shopId = $this->getContextShopId();
        if (!$shopId) {
            return encryptResponse(400, 'error', 'Shop context is required');
        }

        $products = $this->productService->getProductsByPrice($shopId, (float) $request->price);

        return encryptResponse(200, 'success', 'Products retrieved', $products);
    }
}

