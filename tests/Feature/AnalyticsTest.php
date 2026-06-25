<?php

namespace Tests\Feature;

use App\Models\{
    Bill,
    Shop,
    User
};

use App\Enums\UserRole;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => UserRole::ADMIN,
        'is_active' => true,
    ]);
    
    $this->shop1 = Shop::factory()->create();
    $this->shop2 = Shop::factory()->create();
    
    $this->adminToken = auth('api')->tokenById($this->admin->id);
    
    // Create Bills for Shop 1
    // Today bills
    Bill::factory(2)->create([
        'shop_id' => $this->shop1->id, 
        'total' => 100.50,
        'created_at' => Carbon::now()
    ]); // Revenue: 201.00

    // Past month bill (but within this month)
    Bill::factory(1)->create([
        'shop_id' => $this->shop1->id, 
        'total' => 50.00,
        'created_at' => Carbon::now()->startOfMonth()->addDay()
    ]); // Additional month revenue: 50.00 -> Total Month: 251.00

    // Old bill (last month)
    Bill::factory(1)->create([
        'shop_id' => $this->shop1->id, 
        'total' => 200.00,
        'created_at' => Carbon::now()->subMonths(2)
    ]); 

    // Create Bills for Shop 2 (should not reflect in Shop 1)
    Bill::factory(1)->create([
        'shop_id' => $this->shop2->id, 
        'total' => 500.00,
        'created_at' => Carbon::now()
    ]); 
});

it('retrieves correct dashboard analytics scoped to shop', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop1->hashed_id)
        ->postJson('/api/v1/analytics/getdashboardanalytics');

    $response->assertStatus(200)
        ->assertJsonPath('data.bills_count.today', 2)
        ->assertJsonPath('data.bills_count.month', 3)
        // recent_bills should have 3 items max since that's all we created for shop 1 this month (plus 1 last month = 4)
        ->assertJsonCount(4, 'data.recent_bills');

    expect((float) $response->json('data.revenue.today'))->toBe(201.00)
        ->and((float) $response->json('data.revenue.month'))->toBe(251.00);
});

it('requires shop context for analytics', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/analytics/getdashboardanalytics');

    $response->assertStatus(400)
        ->assertJsonPath('message', 'Shop context is required for analytics');
});
