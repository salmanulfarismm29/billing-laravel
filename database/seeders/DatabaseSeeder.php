<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('password'),
            'role' => \App\Enums\UserRole::ADMIN,
        ]);

        // Create 2 Shops
        $shop1 = \App\Models\Shop::factory()->has(\App\Models\Setting::factory())->create(['name' => 'Downtown Shop']);
        $shop2 = \App\Models\Shop::factory()->has(\App\Models\Setting::factory())->create(['name' => 'Uptown Shop']);

        // Give Admin access to shops
        $admin->shops()->attach([$shop1->id, $shop2->id]);
        
        $admin->shop_id = $shop1->id;
        $admin->saveQuietly();

        // Create Cashiers
        $cashier1 = User::factory()->create([
            'email' => 'cashier1@example.com',
            'role' => \App\Enums\UserRole::CASHIER,
            'shop_id' => $shop1->id,
        ]);
        $cashier1->shops()->attach($shop1->id);

        $cashier2 = User::factory()->create([
            'email' => 'cashier2@example.com',
            'role' => \App\Enums\UserRole::CASHIER,
            'shop_id' => $shop2->id,
        ]);
        $cashier2->shops()->attach($shop2->id);

        // Create Products
        $productsShop1 = \App\Models\Product::factory(5)->create(['shop_id' => $shop1->id]);
        $productsShop2 = \App\Models\Product::factory(5)->create(['shop_id' => $shop2->id]);

        // Create Sample Bills properly using relationships to trigger Observers properly
        for ($i = 0; $i < 5; $i++) {
            $bill1 = \App\Models\Bill::factory()->create([
                'shop_id' => $shop1->id,
                'cashier_id' => $cashier1->id,
            ]);
            
            \App\Models\BillItem::factory(3)->create([
                'bill_id' => $bill1->id,
                'product_id' => $productsShop1->random()->id,
            ]);
            
            $bill2 = \App\Models\Bill::factory()->create([
                'shop_id' => $shop2->id,
                'cashier_id' => $cashier2->id,
            ]);
            
            \App\Models\BillItem::factory(2)->create([
                'bill_id' => $bill2->id,
                'product_id' => $productsShop2->random()->id,
            ]);
        }
    }
}
