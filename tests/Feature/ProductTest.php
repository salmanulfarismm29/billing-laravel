<?php

namespace Tests\Feature;

use App\Models\{
    Product,
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
    $this->shop2 = Shop::factory()->create();
    
    $this->adminToken = auth('api')->tokenById($this->admin->id);
    $this->cashierToken = auth('api')->tokenById($this->cashier->id);
});

it('lists all products globally for admin without context', function () {
    Product::factory(2)->create(['shop_id' => $this->shop1->id]);
    Product::factory(3)->create(['shop_id' => $this->shop2->id]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/product/getallproducts');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data.data'); 
});

it('lists products scoped to shop context via header', function () {
    Product::factory(2)->create(['shop_id' => $this->shop1->id]);
    Product::factory(3)->create(['shop_id' => $this->shop2->id]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop1->hashed_id)
        ->postJson('/api/v1/product/getallproducts');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data.data'); 
});

it('allows admin to create a global product', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/product/addproduct', [
            'name' => 'Global Tea',
            'price' => 15.00,
            'is_active' => 1,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Global Tea');
        
    $this->assertDatabaseHas('products', [
        'name' => 'Global Tea',
        'shop_id' => null
    ]);
});

it('allows admin to create a scoped product via header context', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop1->hashed_id)
        ->postJson('/api/v1/product/addproduct', [
            'name' => 'Shop 1 Tea',
            'price' => 25.00,
            'is_active' => 1,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Shop 1 Tea')
        ->assertJsonPath('data.shop_id', $this->shop1->id);
});

it('prevents cashier from creating a product', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->cashierToken)
        ->postJson('/api/v1/product/addproduct', [
            'name' => 'Hack Tea',
            'price' => 10.00,
        ]);

    $response->assertStatus(403);
});

it('prevents viewing a product from a different shop context', function () {
    $product = Product::factory()->create(['shop_id' => $this->shop2->id]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->withHeader('X-Shop-ID', $this->shop1->hashed_id) // Sending shop1 header but requesting shop2 product
        ->postJson('/api/v1/product/getproductinfo', ['hash' => $product->hashed_id]);

    $response->assertStatus(403)
        ->assertJsonPath('message', 'Product does not belong to the active shop');
});

it('allows updating a product', function () {
    $product = Product::factory()->create(['name' => 'Old Tea', 'price' => 20]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/product/updateproduct', [
            'hash' => $product->hashed_id,
            'price' => 22.50,
        ]);

    $response->assertStatus(200);
    expect((float) $response->json('data.price'))->toBe(22.50);
});

it('allows admin to toggle active status of a product', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/product/updateproductstatus', ['hash' => $product->hashed_id]);

    $response->assertStatus(200)
        ->assertJsonPath('data.is_active', false);
});

it('allows admin to soft delete a product', function () {
    $product = Product::factory()->create();

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/product/deleteproduct', ['hash' => $product->hashed_id]);

    $response->assertStatus(200);
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});
