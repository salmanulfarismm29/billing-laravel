<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AnalyticsController,
    AuthController,
    BillController,
    CategoryController,
    ProductController,
    SettingsController,
    ShopController,
    UserController
};

Route::any('/', function () {
    return encryptResponse(401, 'error', 'Unauthorized Access!');
});

Route::middleware(['decrypt-request', 'throttle:api'])->prefix('v1')->group(function () {

    // Public Auth
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('login', 'login');
    });

    // Protected Routes
    Route::middleware('auth:api')->group(function () {
        
        // Authenticated Auth
        Route::controller(AuthController::class)->prefix('auth')->group(function () {
            Route::post('logout', 'logout');
            Route::post('refresh', 'refresh');
            Route::post('me', 'me');
        });

        // Shop Management
        Route::controller(ShopController::class)->prefix('shop')->group(function () {
            Route::post('getallshops', 'getAllShops');
            Route::post('addshop', 'addShop');
            Route::post('getshopinfo', 'getShopInfo');
            Route::post('updateshop', 'updateShop');
            Route::post('updateshopstatus', 'updateShopStatus');
            Route::post('deleteshop', 'deleteShop');
        });

        // User Management
        Route::controller(UserController::class)->prefix('user')->group(function () {
            Route::post('getallusers', 'getAllUsers');
            Route::post('adduser', 'addUser');
            Route::post('getuserinfo', 'getUserInfo');
            Route::post('updateuser', 'updateUser');
            Route::post('updateuserstatus', 'updateUserStatus');
            Route::post('deleteuser', 'deleteUser');
        });

        // Category Management
        Route::controller(CategoryController::class)->prefix('category')->group(function () {
            Route::post('getallcategories', 'getAllCategories');
            Route::post('addcategory', 'addCategory');
            Route::post('updatecategory', 'updateCategory');
            Route::post('deletecategory', 'deleteCategory');
        });

        // Routes scoped to a specific Shop (Tenant)
        Route::middleware('shop.context')->group(function () {
            
            // Product Catalog
            Route::controller(ProductController::class)->prefix('product')->group(function () {
                Route::post('getallproducts', 'getAllProducts');
                Route::post('addproduct', 'addProduct');
                Route::post('getproductinfo', 'getProductInfo');
                Route::post('updateproduct', 'updateProduct');
                Route::post('updateproductstatus', 'updateProductStatus');
                Route::post('deleteproduct', 'deleteProduct');
                
                // Billing Helpers
                Route::post('getpricegroups', 'getPriceGroups');
                Route::post('getproductsbyprice', 'getProductsByPrice');
            });

            // Billing
            Route::controller(BillController::class)->prefix('bill')->group(function () {
                Route::post('getallbills', 'getAllBills');
                Route::post('addbill', 'addBill');
                Route::post('getbillinfo', 'getBillInfo');
                Route::post('deletebill', 'deleteBill');
            });

            // Analytics
            Route::controller(AnalyticsController::class)->prefix('analytics')->group(function () {
                Route::post('getdashboardanalytics', 'getDashboardAnalytics');
            });

            // Settings
            Route::controller(SettingsController::class)->prefix('settings')->group(function () {
                Route::post('getsettings', 'getSettings');
                Route::post('updatesettings', 'updateSettings');
                
                // POS Calculator Configuration
                Route::post('getbillingcalculator', 'getBillingCalculator');
                Route::post('savebillingcalculator', 'saveBillingCalculator');
            });
        });
    });

    Route::any('/test', function () {
        return encryptResponse(200, 'success', 'Tea Billing Api request success');
    });
});
