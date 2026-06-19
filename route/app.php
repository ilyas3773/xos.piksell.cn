<?php
declare(strict_types=1);

use app\index\controller\Shop;
use app\index\controller\User;
use app\index\controller\Index;
use app\index\controller\Install;
use app\index\controller\Payment;
use app\index\controller\WxAuth;
use app\index\middleware\CorsMiddleware;
use app\index\middleware\UserAuthMiddleware;
use think\facade\Route;

Route::rule('install', [Install::class, 'index'], 'GET|POST');
Route::rule('elyas', [Install::class, 'index'], 'GET|POST');

Route::get('/', [Index::class, 'index']);

Route::get('hello/:name', 'index/hello');

Route::options('api/shop/:path', function () {
    return response('', 204);
})->pattern(['path' => '.*'])->middleware(CorsMiddleware::class);

Route::options('api/user/:path', function () {
    return response('', 204);
})->pattern(['path' => '.*'])->middleware(CorsMiddleware::class);

Route::options('api/pay/:path', function () {
    return response('', 204);
})->pattern(['path' => '.*'])->middleware(CorsMiddleware::class);

Route::group('api/shop', function (): void {
    Route::get('home', [Shop::class, 'home']);
    Route::get('faqs', [Shop::class, 'faqs']);
    Route::get('categories', [Shop::class, 'categories']);
    Route::get('products', [Shop::class, 'products']);
    Route::get('product', [Shop::class, 'readProduct']);
    Route::post('product-click', [Shop::class, 'recordProductClick']);
    Route::get('product-resources', [Shop::class, 'productResources']);
    Route::get('products/:id/resources', [Shop::class, 'productResources']);
    Route::get('products/:id', [Shop::class, 'readProduct']);
    Route::post('orders', [Shop::class, 'createOrder'])->middleware(UserAuthMiddleware::class);
    Route::get('orders/active', [Shop::class, 'checkActiveOrder'])->middleware(UserAuthMiddleware::class);
    Route::get('orders/lookup', [Shop::class, 'lookupOrder']);
    Route::get('order', [Shop::class, 'readOrder']);
    Route::get('orders/:id', [Shop::class, 'readOrder']);
})->middleware(CorsMiddleware::class);

Route::group('api/user', function (): void {
    Route::post('register', [User::class, 'register']);
    Route::post('login', [User::class, 'login']);
    Route::post('wx/login', [WxAuth::class, 'login']);

    Route::group('', function (): void {
        Route::get('profile', [User::class, 'profile']);
        Route::put('profile', [User::class, 'updateProfile']);
        Route::post('profile', [User::class, 'updateProfile']);
        Route::post('avatar', [User::class, 'uploadAvatar']);
        Route::post('signin', [User::class, 'signIn']);
        Route::get('orders', [User::class, 'orders']);
        Route::get('energy-logs', [User::class, 'energyLogs']);
        Route::get('energy-sources', [User::class, 'energySources']);
        Route::get('recharge-packages', [User::class, 'rechargePackages']);
        Route::post('recharge-orders', [User::class, 'createRechargeOrder']);
        Route::get('recharge-orders/status', [User::class, 'rechargeOrderStatus']);
        Route::get('wx/info', [WxAuth::class, 'info']);
        Route::post('wx/info', [WxAuth::class, 'updateInfo']);
    })->middleware(UserAuthMiddleware::class);
})->middleware(CorsMiddleware::class);

Route::post('api/pay/wechat/energy-recharge/notify', [Payment::class, 'wechatEnergyRechargeNotify'])->middleware(CorsMiddleware::class);
Route::any('api/pay/epay/energy-recharge/notify', [Payment::class, 'epayEnergyRechargeNotify'])->middleware(CorsMiddleware::class);
Route::get('api/pay/epay/energy-recharge/return', [Payment::class, 'epayEnergyRechargeReturn'])->middleware(CorsMiddleware::class);
