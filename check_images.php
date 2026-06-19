<?php
require __DIR__ . '/vendor/autoload.php';

$app = new \think\App();
$app->initialize();

$products = \think\facade\Db::name('product')->field('id,name,cover_image,gallery_images')->limit(5)->select()->toArray();

echo "=== 商品图片字段 ===\n";
foreach ($products as $product) {
    echo "ID: {$product['id']}, 名称: {$product['name']}\n";
    echo "  cover_image: {$product['cover_image']}\n";
    echo "  gallery_images: {$product['gallery_images']}\n\n";
}
