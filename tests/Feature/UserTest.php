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
    
    $this->shop1 = Shop::factory()->create();
    $this->shop2 = Shop::factory()->create();
    
    $this->adminToken = auth('api')->tokenById($this->admin->id);
    $this->cashierToken = auth('api')->tokenById($this->cashier->id);
});

it('allows admin to list users', function () {
    User::factory(3)->create();

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/user/getallusers');

    $response->assertStatus(200)
        // 5 users total (3 new + admin + cashier)
        ->assertJsonCount(5, 'data.data'); 
});

it('allows admin to create a user and assign to shops', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/user/adduser', [
            'name' => 'New Manager',
            'email' => 'manager@test.com',
            'password' => 'password123',
            'role' => 2,
            'is_active' => 1,
            'shop_ids' => [$this->shop1->id, $this->shop2->id],
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'New Manager')
        ->assertJsonPath('data.email', 'manager@test.com');
        
    $this->assertDatabaseHas('users', [
        'email' => 'manager@test.com',
        'shop_id' => $this->shop1->id // first shop is primary
    ]);
    
    $user = User::where('email', 'manager@test.com')->first();
    expect($user->shops)->toHaveCount(2);
});

it('prevents non-admin from creating a user', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->cashierToken)
        ->postJson('/api/v1/user/adduser', [
            'name' => 'Hack User',
            'email' => 'hack@test.com',
            'password' => 'password123',
            'role' => 3,
            'shop_ids' => [$this->shop1->id],
        ]);

    $response->assertStatus(403);
});

it('allows viewing a specific user by hashed ID', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/user/getuserinfo', ['hash' => $this->cashier->hashed_id]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', $this->cashier->name);
});

it('allows admin to update a user', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/user/updateuser', [
            'hash' => $this->cashier->hashed_id,
            'name' => 'Updated Cashier',
            'shop_ids' => [$this->shop2->id], // Move to shop 2
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Cashier');
        
    expect($this->cashier->fresh()->shops)->toHaveCount(1)
        ->and($this->cashier->fresh()->shop_id)->toBe($this->shop2->id);
});

it('allows admin to toggle active status of a user', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/user/updateuserstatus', ['hash' => $this->cashier->hashed_id]);

    $response->assertStatus(200)
        ->assertJsonPath('data.is_active', false);
        
    expect($this->cashier->fresh()->is_active)->toBeFalse();
});

it('prevents admin from deactivating themselves', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/user/updateuserstatus', ['hash' => $this->admin->hashed_id]);

    $response->assertStatus(403)
        ->assertJsonPath('message', 'Cannot toggle own active status');
});

it('allows admin to delete a user', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
        ->postJson('/api/v1/user/deleteuser', ['hash' => $this->cashier->hashed_id]);

    $response->assertStatus(200);
    $this->assertSoftDeleted('users', ['id' => $this->cashier->id]);
});
