<?php

namespace App\Http\Controllers;

use App\Http\Requests\{
    SaveBillingCalculatorRequest,
    UpdateSettingRequest
};
use App\Models\Shop;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsService $settingsService
    ) {}

    /**
     * Resolve the active shop ID from the container binding set by middleware.
     */
    protected function getContextShopId(): ?int
    {
        return app()->bound(Shop::class) ? app(Shop::class)->id : null;
    }

    /**
     * Display the settings for the current shop context.
     */
    public function getSettings(): JsonResponse
    {
        $shopId = $this->getContextShopId();
        if (!$shopId) {
            return encryptResponse(400, 'error', 'Shop context is required for settings');
        }

        $settings = $this->settingsService->getSettingsByShopId($shopId);

        return encryptResponse(200, 'success', 'Settings retrieved', $settings);
    }

    /**
     * Update the settings in storage.
     */
    public function updateSettings(UpdateSettingRequest $request): JsonResponse
    {
        $shopId = $this->getContextShopId();
        if (!$shopId) {
            return encryptResponse(400, 'error', 'Shop context is required for settings');
        }

        $settings        = $this->settingsService->getSettingsByShopId($shopId);
        $updatedSettings = $this->settingsService->updateSettings($settings, $request->validated());

        return encryptResponse(200, 'success', 'Settings updated successfully', $updatedSettings);
    }

    /**
     * GET /api/v1/settings/getbillingcalculator
     *
     * Returns the currently saved shortcut price array for the calculator UI.
     * Returns an empty array when none are configured yet.
     */
    public function getBillingCalculator(): JsonResponse
    {
        $shopId = $this->getContextShopId();
        if (!$shopId) {
            return encryptResponse(400, 'error', 'Shop context is required for settings');
        }

        $prices = $this->settingsService->getBillingCalculatorPrices($shopId);

        return encryptResponse(200, 'success', 'Billing calculator settings retrieved', [
            'selectedPrices' => $prices,
        ]);
    }

    /**
     * POST /api/v1/settings/savebillingcalculator
     *
     * Persists the admin's chosen shortcut price layout (up to 10 prices).
     * The order supplied is preserved so the POS grid buttons render in that exact order.
     */
    public function saveBillingCalculator(SaveBillingCalculatorRequest $request): JsonResponse
    {
        $shopId = $this->getContextShopId();
        if (!$shopId) {
            return encryptResponse(400, 'error', 'Shop context is required for settings');
        }

        $saved = $this->settingsService->saveBillingCalculatorPrices(
            $shopId,
            $request->validated('selectedPrices')
        );

        return encryptResponse(200, 'success', 'Billing calculator settings saved', [
            'selectedPrices' => $saved,
        ]);
    }
}


