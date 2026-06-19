<?php
declare(strict_types=1);

use think\facade\Route;

Route::get('health', 'Dashboard/health');

Route::group('auth', function (): void {
    Route::post('login', 'Auth/login');
    Route::get('profile', 'Auth/profile');
});

Route::group('dashboard', function (): void {
    Route::get('stats', 'Dashboard/stats');
});

Route::group('data-statistics', function (): void {
    Route::get('', 'DataStatistic/index');
    Route::get('rankings', 'DataStatistic/index');
    Route::get('exchange-ranking', 'DataStatistic/exchangeRanking');
    Route::get('click-ranking', 'DataStatistic/clickRanking');
    Route::get('search-ranking', 'DataStatistic/searchRanking');
});

Route::group('product-search-logs', function (): void {
    Route::get('', 'ProductSearchLog/index');
    Route::get('analysis', 'ProductSearchLog/analysisData');
});

Route::group('upload', function (): void {
    Route::post('image', 'Upload/image');
});

Route::group('categories', function (): void {
    Route::get('', 'Category/index');
    Route::post('', 'Category/save');
    Route::post('batch', 'Category/saveBatch');
    Route::post('delete-batch', 'Category/deleteBatch');
    Route::put(':id', 'Category/update');
    Route::delete(':id', 'Category/delete');
});

Route::group('products', function (): void {
    Route::get('', 'Product/index');
    Route::get('card-resources', 'Product/getCardResources');
    Route::post('toggle-featured-batch', 'Product/batchToggleFeatured');
    Route::get(':id', 'Product/read');
    Route::post('', 'Product/save');
    Route::post('delete-batch', 'Product/deleteBatch');
    Route::put(':id/featured', 'Product/toggleFeatured');
    Route::put(':id', 'Product/update');
    Route::delete(':id', 'Product/delete');
    Route::delete(':id/card-resource', 'Product/unbindCardResource');
});

Route::group('product-metrics', function (): void {
    Route::get('', 'ProductMetric/index');
    Route::post('backfill', 'ProductMetric/backfill');
});

Route::group('wishes', function (): void {
    Route::get('', 'Wish/index');
    Route::put(':id', 'Wish/updateStatus');
    Route::delete(':id', 'Wish/delete');
});

Route::group('cards', function (): void {
    Route::get('', 'Card/index');
    Route::get(':id', 'Card/read');
    Route::post('batch', 'Card/saveBatch');
    Route::put(':id/status', 'Card/updateStatus');
    Route::delete(':id', 'Card/delete');
});

Route::group('card-resources', function (): void {
    Route::get('', 'CardResource/index');
    Route::get(':id', 'CardResource/read');
    Route::post('', 'CardResource/save');
    Route::put(':id', 'CardResource/update');
    Route::delete(':id', 'CardResource/delete');
});

Route::group('orders', function (): void {
    Route::get('', 'Order/index');
    Route::get(':id', 'Order/read');
    Route::post('', 'Order/save');
    Route::put(':id', 'Order/update');
    Route::delete(':id', 'Order/delete');
    Route::put(':id/status', 'Order/updateStatus');
    Route::post(':id/deliver', 'Order/deliver');
});

Route::group('users', function (): void {
    Route::get('', 'User/index');
    Route::get(':id', 'User/read');
    Route::post('', 'User/save');
    Route::put(':id', 'User/update');
    Route::delete(':id', 'User/delete');
    Route::post(':id/adjust-energy', 'User/adjustEnergy');
});

Route::group('energy-logs', function (): void {
    Route::get('', 'EnergyLog/index');
});

Route::group('energy-sources', function (): void {
    Route::get('', 'EnergySource/index');
    Route::get(':id', 'EnergySource/read');
    Route::post('', 'EnergySource/save');
    Route::put(':id', 'EnergySource/update');
    Route::delete(':id', 'EnergySource/delete');
});

Route::group('energy-packages', function (): void {
    Route::get('', 'EnergyRechargePackage/index');
    Route::get(':id', 'EnergyRechargePackage/read');
    Route::post('', 'EnergyRechargePackage/save');
    Route::put(':id', 'EnergyRechargePackage/update');
    Route::delete(':id', 'EnergyRechargePackage/delete');
});

Route::group('configs', function (): void {
    Route::get('', 'Config/index');
    Route::post('group/:group', 'Config/saveGroup');
    Route::put('group/:group', 'Config/saveGroup');
});

Route::group('site-contents', function (): void {
    Route::get(':key', 'SiteContent/read');
    Route::put(':key', 'SiteContent/update');
    Route::post(':key', 'SiteContent/update');
});

Route::group('faqs', function (): void {
    Route::get('', 'Faq/index');
    Route::get(':id', 'Faq/read');
    Route::post('', 'Faq/save');
    Route::put(':id', 'Faq/update');
    Route::delete(':id', 'Faq/delete');
});

Route::group('announcements', function (): void {
    Route::get('', 'Announcement/index');
    Route::get(':id', 'Announcement/read');
    Route::post('', 'Announcement/save');
    Route::put(':id', 'Announcement/update');
    Route::delete(':id', 'Announcement/delete');
});
