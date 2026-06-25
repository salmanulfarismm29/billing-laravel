<?php

namespace Tests\Feature;

use App\Models\{
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
    
    $this->adminToken = auth('api')->tokenById($this->admin->id);
    $this->cashierToken = auth('api')->tokenById($this->cashier->id);
});

it('allows admin to list shops', function () {
    Shop::factory(3)->create();

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/shop/getallshops');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data.data'); // paginator data array
});

it('allows admin to create a shop', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/shop/addshop', [
            'name' => 'New Shop',
            'location' => 'City Center',
            'is_active' => 1,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'New Shop');
        
    $this->assertDatabaseHas('shops', ['name' => 'New Shop']);
});

it('prevents non-admin from creating a shop', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->cashierToken)
        ->postJson('/api/v1/shop/addshop', [
            'name' => 'New Shop',
            'location' => 'City Center',
        ]);

    $response->assertStatus(403);
});

it('allows viewing a specific shop by hashed ID', function () {
    $shop = Shop::factory()->create();

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/shop/getshopinfo', ['hash' => $shop->hashed_id]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', $shop->name);
});

it('returns 404 for invalid hashed ID', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/shop/getshopinfo', ['hash' => 'InvalidHash123']);

    $response->assertStatus(404);
});

it('allows admin to update a shop', function () {
    $shop = Shop::factory()->create(['name' => 'Old Name']);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/shop/updateshop', [
            'hash' => $shop->hashed_id,
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Name');
});

it('allows admin to toggle active status of a shop', function () {
    $shop = Shop::factory()->create(['is_active' => true]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/shop/updateshopstatus', ['hash' => $shop->hashed_id]);

    $response->assertStatus(200)
        ->assertJsonPath('data.is_active', false);
        
    expect($shop->fresh()->is_active)->toBeFalse();
});

it('allows admin to soft delete a shop', function () {
    $shop = Shop::factory()->create();

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/shop/deleteshop', ['hash' => $shop->hashed_id]);

    $response->assertStatus(200);
    $this->assertSoftDeleted('shops', ['id' => $shop->id]);
});
