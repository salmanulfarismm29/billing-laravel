<?php

namespace Tests\Feature;

use App\Models\{
    Product,
    Setting,
    Shop,
    User
};

use App\Enums\UserRole;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role'      => UserRole::ADMIN,
        'is_active' => true,
    ]);

    $this->cashier = User::factory()->create([
        'role'      => UserRole::CASHIER,
        'is_active' => true,
    ]);

    $this->shop = Shop::factory()->create();

    $this->adminToken   = auth('api')->tokenById($this->admin->id);
    $this->cashierToken = auth('api')->tokenById($this->cashier->id);

    // Create products with varied price points in this shop
    Product::factory()->count(3)->create(['shop_id' => $this->shop->id, 'price' => 10.00, 'is_active' => true]);
    Product::factory()->count(4)->create(['shop_id' => $this->shop->id, 'price' => 15.00, 'is_active' => true]);
    Product::factory()->count(2)->create(['shop_id' => $this->shop->id, 'price' => 25.00, 'is_active' => true]);

    // One inactive product — must NOT appear in price groups
    Product::factory()->create(['shop_id' => $this->shop->id, 'price' => 50.00, 'is_active' => false]);
});

// ─── Price Groups ────────────────────────────────────────────────────────────

it('returns price groups sorted ascending with counts when no shortcuts configured', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/product/getpricegroups');

    $response->assertStatus(200);

    $groups = $response->json('data');

    // Expect only 3 active price tiers; ₹50 (inactive) must be absent
    expect($groups)->toHaveCount(3);

    // Without any selected shortcuts, all isSelected = false
    expect(collect($groups)->pluck('isSelected')->unique()->all())->toBe([false]);

    // Prices should be sorted ascending when nothing is selected
    expect(array_map('floatval', collect($groups)->pluck('price')->values()->all()))->toBe([10.0, 15.0, 25.0]);

    // Product counts must match what we seeded
    $byPrice = collect($groups)->keyBy('price');
    expect((int) $byPrice[10.0]['productCount'])->toBe(3);
    expect((int) $byPrice[15.0]['productCount'])->toBe(4);
    expect((int) $byPrice[25.0]['productCount'])->toBe(2);
});

it('bubbles selected prices to the top of the price group list', function () {
    // Pre-configure ₹25 and ₹10 as shortcuts (in this order)
    $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/settings/savebillingcalculator', ['selectedPrices' => [25, 10]]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/product/getpricegroups');

    $response->assertStatus(200);

    $prices = collect($response->json('data'))->pluck('price')->values()->all();

    // ₹25 and ₹10 come first (in admin-chosen order), ₹15 follows
    expect((float) $prices[0])->toBe(25.0);
    expect((float) $prices[1])->toBe(10.0);
    expect((float) $prices[2])->toBe(15.0);


    // isSelected must be true for the configured prices only
    $selected = collect($response->json('data'))->where('isSelected', true)->pluck('price')->all();
    expect(sort($selected))->toBeTruthy();
    expect(collect($selected)->contains(25.0))->toBeTrue();
    expect(collect($selected)->contains(10.0))->toBeTrue();
});

// ─── Billing Calculator Settings ─────────────────────────────────────────────

it('returns empty selectedPrices before any configuration', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/settings/getbillingcalculator');

    $response->assertStatus(200)
        ->assertJsonPath('data.selectedPrices', []);
});

it('allows admin to save and retrieve billing calculator prices', function () {
    $prices = [10, 15, 25];

    $saveResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/settings/savebillingcalculator', ['selectedPrices' => $prices]);

    $saveResponse->assertStatus(200);

    // Verify they are persisted correctly
    $getResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/settings/getbillingcalculator');

    $getResponse->assertStatus(200);

    $saved = $getResponse->json('data.selectedPrices');
    expect(array_map('floatval', $saved))->toBe([10.0, 15.0, 25.0]);
});

it('prevents saving more than 10 price shortcuts', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/settings/savebillingcalculator', [
            'selectedPrices' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], // 11 items — must fail
        ]);

    $response->assertStatus(406)
        ->assertJsonPath('status', 'error');
});

it('prevents non-admins from saving billing calculator settings', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->cashierToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/settings/savebillingcalculator', ['selectedPrices' => [10, 15]]);

    $response->assertStatus(403);
});

// ─── Products By Price ────────────────────────────────────────────────────────

it('returns all active products at an exact price point', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->cashierToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/product/getproductsbyprice', ['price' => 15]);

    $response->assertStatus(200);

    // Four products seeded at ₹15
    expect($response->json('data'))->toHaveCount(4);
});

it('returns empty array when no products match a price point', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->cashierToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/product/getproductsbyprice', ['price' => 999]);

    $response->assertStatus(200);

    expect($response->json('data'))->toBeEmpty();
});

it('does not return inactive products in by-price lookup', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->cashierToken)
        ->withHeader('X-Shop-ID', $this->shop->hashed_id)
        ->postJson('/api/v1/product/getproductsbyprice', ['price' => 50]); // only inactive products here

    $response->assertStatus(200);

    expect($response->json('data'))->toBeEmpty();
});
