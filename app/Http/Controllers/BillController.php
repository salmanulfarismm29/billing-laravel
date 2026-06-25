<?php

namespace App\Http\Controllers;

use App\Models\{
    Bill,
    Shop
};

use App\Enums\UserRole;

use App\Http\Requests\StoreBillRequest;
use App\Services\BillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function __construct(
        protected BillService $billService
    ) {}

    /**
     * Get the resolved shop ID context from the middleware binding.
     */
    protected function getContextShopId(): ?int
    {
        return app()->bound(Shop::class) ? app(Shop::class)->id : null;
    }

    /**
     * Display a listing of bills.
     */
    public function getAllBills(): JsonResponse
    {
        $shopId = $this->getContextShopId();

        // Enforce shop scoping for cashiers/managers if needed
        if (!$shopId && auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Shop context is required');
        }

        $bills = $this->billService->getPaginated($shopId);
        return encryptResponse(200, 'success', 'Bills retrieved', $bills);
    }

    /**
     * Store a newly created bill.
     */
    public function addBill(StoreBillRequest $request): JsonResponse
    {
        $shopId = $this->getContextShopId();

        if (!$shopId) {
            return encryptResponse(400, 'error', 'Shop context is required to create a bill');
        }

        $cashierId = auth('api')->id();

        try {
            $bill = $this->billService->createBill($request->validated(), $shopId, $cashierId);
            return encryptResponse(201, 'success', 'Bill created successfully', $bill);
        } catch (\Exception $e) {
            return encryptResponse(400, 'error', $e->getMessage());
        }
    }

    /**
     * Display the specified bill.
     */
    public function getBillInfo(Request $request): JsonResponse
    {
        $id = Bill::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'Bill not found');
        }

        $shopId = $this->getContextShopId();
        
        try {
            $bill = $this->billService->getBillById($id, $shopId);
            return encryptResponse(200, 'success', 'Bill retrieved', $bill);
        } catch (\Exception $e) {
            return encryptResponse(404, 'error', 'Bill not found in this shop context');
        }
    }

    /**
     * Remove the specified bill from storage.
     */
    public function deleteBill(Request $request): JsonResponse
    {
        if (auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $id = Bill::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'Bill not found');
        }

        $shopId = $this->getContextShopId();
        
        try {
            $bill = $this->billService->getBillById($id, $shopId);
            $this->billService->deleteBill($bill);
            return encryptResponse(200, 'success', 'Bill deleted successfully');
        } catch (\Exception $e) {
            return encryptResponse(404, 'error', 'Bill not found');
        }
    }
}

