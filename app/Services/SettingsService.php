<?php

namespace App\Services;

use App\Models\{
    Product,
    Setting
};

use Illuminate\Support\Collection;

class SettingsService
{
    /**
     * Get settings for a shop, auto-create with defaults if not yet configured.
     */
    public function getSettingsByShopId(int $shopId): Setting
    {
        return Setting::firstOrCreate(
            ['shop_id' => $shopId],
            [
                'ask_customer_details'      => true,
                'ask_payment_method'        => true,
                'billing_calculator_prices' => null,
            ]
        );
    }

    /**
     * Update shop settings (general).
     */
    public function updateSettings(Setting $setting, array $data): Setting
    {
        $setting->update($data);
        return $setting;
    }

    /**
     * Retrieve the currently saved list of billing calculator shortcut prices.
     *
     * Returns an empty array if none have been configured yet.
     */
    public function getBillingCalculatorPrices(int $shopId): array
    {
        $setting = $this->getSettingsByShopId($shopId);

        return $setting->billing_calculator_prices ?? [];
    }

    /**
     * Persist the chosen shortcut price array for this shop's billing calculator.
     *
     * The prices are stored as a plain JSON array so the frontend POS component
     * can render the exact grid buttons in the exact order the admin intended.
     */
    public function saveBillingCalculatorPrices(int $shopId, array $prices): array
    {
        // Ensure values are cast to floats for consistent storage/comparison
        $normalised = array_values(array_map('floatval', $prices));

        $setting = $this->getSettingsByShopId($shopId);
        $setting->update(['billing_calculator_prices' => $normalised]);

        return $normalised;
    }
}
