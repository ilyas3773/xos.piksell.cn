<?php
declare(strict_types=1);

use app\index\middleware\UserAuthMiddleware;
use think\facade\Route;

Route::rule('install', 'Install/index', 'GET|POST');
Route::rule('elyas', 'Install/index', 'GET|POST');

// 紧急管理员密码重置通道：GET /_reset_admin?username=xxx&password=yyy
// 救命用的临时通道。修好后请删除或鉴权。
Route::get('_reset_admin', function () {
    $svc = new \app\service\InstallerService();
    $u = request()->get('username');
    $p = request()->get('password');
    $result = $svc->selfResetAdmin(is_string($u) ? $u : null, is_string($p) ? $p : null);
    return json($result, 200, ['Content-Type' => 'application/json; charset=utf-8']);
});

Route::group('api/shop', function (): void {
    Route::get('home', 'Shop/home');
    Route::get('faqs', 'Shop/faqs');
    Route::get('categories', 'Shop/categories');
    Route::get('products', 'Shop/products');
    Route::get('products/featured', 'Shop/featuredProducts');
    Route::get('product', 'Shop/readProduct');
    Route::get('products/:id/resources', 'Shop/productResources');
    Route::get('products/:id', 'Shop/readProduct');
    Route::post('orders', 'Shop/createOrder')->middleware(UserAuthMiddleware::class);
    Route::get('orders/active', 'Shop/checkActiveOrder')->middleware(UserAuthMiddleware::class);
    Route::get('order', 'Shop/readOrder');
    Route::get('orders/lookup', 'Shop/lookupOrder');
    Route::get('orders/:id', 'Shop/readOrder');
    Route::post('wishes', 'Shop/submitWish');
    Route::get('contact', 'Shop/customerContact');
});

Route::group('api/user', function (): void {
    Route::post('register', 'User/register');
    Route::post('login', 'User/login');
    
    // 微信小程序登录
    Route::post('wx/login', 'WxAuth/login');

    Route::group('', function (): void {
        Route::get('profile', 'User/profile');
        Route::put('profile', 'User/updateProfile');
        Route::post('profile', 'User/updateProfile');
        Route::post('avatar', 'User/uploadAvatar');
        Route::post('signin', 'User/signIn');
        Route::get('orders', 'User/orders');
        Route::get('energy-logs', 'User/energyLogs');
        Route::get('energy-sources', 'User/energySources');
        Route::get('recharge-packages', 'User/rechargePackages');
        Route::post('recharge-orders', 'User/createRechargeOrder');
        Route::get('recharge-orders/status', 'User/rechargeOrderStatus');
        
        // 微信用户信息
        Route::get('wx/info', 'WxAuth/info');
        Route::post('wx/info', 'WxAuth/updateInfo');
    })->middleware(UserAuthMiddleware::class);
});

Route::post('api/pay/wechat/energy-recharge/notify', 'Payment/wechatEnergyRechargeNotify');
Route::any('api/pay/epay/energy-recharge/notify', 'Payment/epayEnergyRechargeNotify');
Route::get('api/pay/epay/energy-recharge/return', 'Payment/epayEnergyRechargeReturn');
