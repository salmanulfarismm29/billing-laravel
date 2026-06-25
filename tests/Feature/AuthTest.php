<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a user to log in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'login' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'code',
            'status',
            'message',
            'data' => [
                'id',
                'hashed_id',
                'name',
                'email',
                'role_id',
                'shop_hashed_id',
                'token',
            ]
        ]);
});

it('prevents an inactive user from logging in', function () {
    $user = User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => bcrypt('password123'),
        'is_active' => false,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'login' => 'inactive@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(406)
        ->assertJson([
            'code' => 406,
            'status' => 'error',
            'message' => 'Inactive user please connect admin.',
        ]);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'login' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(406);
});

it('allows an authenticated user to fetch their details', function () {
    $user = User::factory()->create();
    
    // JWT login helper or just standard actingAs works for some, 
    // but with JWT it is better to login via the endpoint to get the token.
    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonPath('data.email', $user->email);
});

it('allows an authenticated user to logout', function () {
    $user = User::factory()->create();
    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Successfully logged out');
        
    // Token should be invalidated
    $checkResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/auth/me');
        
    $checkResponse->assertStatus(401);
});

it('allows an authenticated user to refresh their token', function () {
    $user = User::factory()->create();
    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/auth/refresh');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'access_token'
            ]
        ]);
        
    $newToken = $response->json('data.access_token');
    expect($newToken)->not->toBe($token);
});
