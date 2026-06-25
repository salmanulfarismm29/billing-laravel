<?php

namespace Tests\Feature;

use App\Models\{
    Setting,
    Shop,
    User
};

use App\Enums\UserRole;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => UserRole::ADMIN,
        'is_active' => true,
    ]);
    
    $this->cashier = User::factory()->create([
        'role' => UserRole::CASHIER,
        'is_active' => true,
    ]);
    
    $this->shop1 = Shop::factory()->create();
    
    $this->adminToken = auth('api')->tokenById($this->admin->id);
    $this->cashierToken = auth('api')->tokenById($this->cashier->id);
});

it('auto-creates and retrieves default settings for a shop', function () {
    // Assert no setting exists yet
    $this->assertDatabaseMissing('settings', ['shop_id' => $this->shop1->id]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop1->hashed_id)
        ->postJson('/api/v1/settings/getsettings');

    $response->assertStatus(200)
        ->assertJsonPath('data.ask_customer_details', true)
        ->assertJsonPath('data.ask_payment_method', true);
        
    // Now it should exist
    $this->assertDatabaseHas('settings', [
        'shop_id' => $this->shop1->id,
        'ask_customer_details' => true
    ]);
});

it('allows admin to update shop settings', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop1->hashed_id)
        ->postJson('/api/v1/settings/updatesettings', [
            'ask_customer_details' => false,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.ask_customer_details', false)
        ->assertJsonPath('data.ask_payment_method', true);
        
    $this->assertDatabaseHas('settings', [
        'shop_id' => $this->shop1->id,
        'ask_customer_details' => false,
        'ask_payment_method' => true
    ]);
});

it('prevents non-admins from updating settings', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->cashierToken)
        ->withHeader('X-Shop-ID', $this->shop1->hashed_id)
        ->postJson('/api/v1/settings/updatesettings', [
            'ask_customer_details' => false,
        ]);

    $response->assertStatus(403);
});
