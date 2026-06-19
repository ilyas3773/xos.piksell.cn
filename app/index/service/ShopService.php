<?php
declare(strict_types=1);

namespace app\index\service;

use app\admin\service\CategoryService;
use app\admin\service\ProductService;
use app\model\Announcement;
use app\model\Card;
use app\model\CardOrder;
use app\model\CardResource;
use app\model\Category;
use app\model\Faq;
use app\model\Product;
use app\model\ProductSearchLog;
use app\model\ProductSearchLogItem;
use app\model\SystemConfig;
use app\service\ProductMetricService;
use app\service\UserEnergyService;
use RuntimeException;
use think\facade\Db;

class ShopService
{
    public function getHome(): array
    {
        $websiteConfig = $this->getWebsiteConfig();
        $metricReady = (new ProductMetricService())->isReady();
        $productRelations = $this->productRelations($metricReady);
        $latestAnnouncement = Announcement::where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'desc')
            ->find();

        // 限定首页"热门游戏"和"最新游戏"只展示游戏分类，不夹杂应用
        $gameCategoryIds = $this->resolveGameCategoryIds();

        $bannerProducts = Product::with($productRelations)
            ->where('status', 1)
            ->when(!empty($gameCategoryIds), function ($q) use ($gameCategoryIds) {
                $q->whereIn('category_id', $gameCategoryIds);
            })
            ->order('id', 'desc')
            ->limit(4)
            ->select();

        $featuredQuery = Product::with($productRelations)
            ->where('status', 1);
        if (!empty($gameCategoryIds)) {
            $featuredQuery->whereIn('category_id', $gameCategoryIds);
        }
        if ($metricReady) {
            $featuredQuery
                ->orderRaw('(COALESCE((SELECT click_count FROM product_metrics WHERE product_metrics.product_id = products.id), 0) + COALESCE((SELECT exchange_count FROM product_metrics WHERE product_metrics.product_id = products.id), 0)) DESC')
                ->orderRaw('COALESCE((SELECT exchange_count FROM product_metrics WHERE product_metrics.product_id = products.id), 0) DESC')
                ->orderRaw('COALESCE((SELECT click_count FROM product_metrics WHERE product_metrics.product_id = products.id), 0) DESC');
        }
        $featuredProducts = $featuredQuery->order('created_at', 'desc')->order('id', 'desc')->limit(8)->select();

        $latestQuery = Product::with($productRelations)
            ->where('status', 1);
        if (!empty($gameCategoryIds)) {
            $latestQuery->whereIn('category_id', $gameCategoryIds);
        }
        $latestProducts = $latestQuery
            ->order('created_at', 'desc')
            ->order('id', 'desc')
            ->limit(8)
            ->select();

        return [
            'banners' => array_map(function ($item): array {
                $product = $this->formatProduct($item->toArray());

                return [
                    'id' => (int)$product['id'],
                    'title' => (string)$product['name'],
                    'subtitle' => (string)($product['name_en'] ?: $product['compatibility']),
                    'image' => (string)$product['display_image'],
                ];
            }, $bannerProducts->all()),
            'type_categories' => $this->getRootCategories(CategoryService::GROUP_TYPE),
            'kind_categories' => $this->getRootCategories(CategoryService::GROUP_KIND),
            'featured_products' => array_map(
                fn ($item): array => $this->formatProduct($item->toArray()),
                $featuredProducts->all()
            ),
            'latest_products' => array_map(
                fn ($item): array => $this->formatProduct($item->toArray()),
                $latestProducts->all()
            ),
            'announcement' => $latestAnnouncement ? [
                'id' => (int)$latestAnnouncement->id,
                'title' => (string)$latestAnnouncement->title,
                'summary' => (string)$latestAnnouncement->summary,
                'content' => (string)$latestAnnouncement->content,
            ] : null,
            'service' => [
                'site_name' => $websiteConfig['site_name'] !== '' ? $websiteConfig['site_name'] : 'Piksell Card Store',
                'tagline' => $websiteConfig['site_tagline'] !== '' ? $websiteConfig['site_tagline'] : 'Browse products, place orders, and check results in one place.',
                'logo' => $websiteConfig['site_logo'],
                'icon' => $websiteConfig['site_icon'],
                'share_image' => $websiteConfig['default_share_image'],
                'home_banner_image' => $websiteConfig['home_banner_image'],
                'customer_qr_image' => $websiteConfig['customer_qr_image'],
                'short_intro' => $websiteConfig['site_tagline'],
                'intro' => $websiteConfig['site_intro'],
                'service_notice' => $websiteConfig['service_notice'],
                'record_number' => $websiteConfig['record_number'],
                'copyright_text' => $websiteConfig['copyright_text'],
                'contact_email' => $websiteConfig['contact_email'],
                'seo_keywords' => $websiteConfig['seo_keywords'],
                'seo_description' => $websiteConfig['seo_description'],
            ],
        ];
    }

    public function getCategories(string $group = 'all'): array
    {
        $normalized = trim(strtolower($group));
        if ($normalized === CategoryService::GROUP_TYPE) {
            return [
                'group_key' => CategoryService::GROUP_TYPE,
                'tree' => $this->getCategoryTree(CategoryService::GROUP_TYPE),
            ];
        }

        if ($normalized === CategoryService::GROUP_KIND) {
            return [
                'group_key' => CategoryService::GROUP_KIND,
                'tree' => $this->getCategoryTree(CategoryService::GROUP_KIND),
            ];
        }

        return [
            'group_key' => 'all',
            'type_tree' => $this->getCategoryTree(CategoryService::GROUP_TYPE),
            'kind_tree' => $this->getCategoryTree(CategoryService::GROUP_KIND),
        ];
    }

    public function getFaqs(): array
    {
        $rows = Faq::where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();

        return [
            'list' => array_map(function ($item): array {
                $row = $item->toArray();

                return [
                    'id' => (int)($row['id'] ?? 0),
                    'question' => (string)($row['question'] ?? ''),
                    'answer' => (string)($row['answer'] ?? ''),
                    'image' => (string)($row['image'] ?? ''),
                    'sort' => (int)($row['sort'] ?? 0),
                ];
            }, $rows->all()),
        ];
    }

    public function getProducts(array $filters): array
    {
        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = max(1, min(50, (int)($filters['limit'] ?? 12)));
        $keyword = trim((string)($filters['keyword'] ?? ''));
        $categoryId = (int)($filters['category_id'] ?? 0);
        $kindCategoryId = (int)($filters['kind_category_id'] ?? 0);
        $isFeatured = (int)($filters['is_featured'] ?? 0);
        $sort = trim((string)($filters['sort'] ?? 'latest'));
        if (!in_array($sort, ['hot', 'click', 'exchange', 'latest'], true)) {
            $sort = 'latest';
        }

        $metricReady = (new ProductMetricService())->isReady();
        $query = Product::with($this->productRelations($metricReady))
            ->where('status', 1);

        if ($isFeatured === 1) {
            $query->where('is_featured', 1);
        }

        if ($keyword !== '') {
            $matchFields = trim((string)($filters['match_fields'] ?? ''));
            $this->applyProductKeywordFilter($query, $keyword, $matchFields);
        }

        $categoryService = new CategoryService();
        if ($categoryId > 0) {
            $categoryIds = array_values(array_unique(array_merge(
                [$categoryId],
                $categoryService->getDescendantIds($categoryId)
            )));
            $query->whereIn('category_id', $categoryIds);
        }

        if ($kindCategoryId > 0) {
            $kindCategoryIds = array_values(array_unique(array_merge(
                [$kindCategoryId],
                $categoryService->getDescendantIds($kindCategoryId)
            )));
            $query->whereIn('kind_category_id', $kindCategoryIds);
        }

        if ($metricReady && $sort === 'hot') {
            $query
                ->orderRaw('(COALESCE((SELECT click_count FROM product_metrics WHERE product_metrics.product_id = products.id), 0) + COALESCE((SELECT exchange_count FROM product_metrics WHERE product_metrics.product_id = products.id), 0)) DESC')
                ->orderRaw('COALESCE((SELECT exchange_count FROM product_metrics WHERE product_metrics.product_id = products.id), 0) DESC')
                ->orderRaw('COALESCE((SELECT click_count FROM product_metrics WHERE product_metrics.product_id = products.id), 0) DESC');
        } elseif ($metricReady && $sort === 'click') {
            $query->orderRaw('COALESCE((SELECT click_count FROM product_metrics WHERE product_metrics.product_id = products.id), 0) DESC');
        } elseif ($metricReady && $sort === 'exchange') {
            $query->orderRaw('COALESCE((SELECT exchange_count FROM product_metrics WHERE product_metrics.product_id = products.id), 0) DESC');
        }
        if ($sort === 'latest') {
            $query->order('created_at', 'desc');
        }
        $query->order('id', 'desc');

        $total = (clone $query)->count();
        $rows = $query->page($page, $limit)->select();
        $items = $rows->all();
        if ($keyword !== '') {
            (new ProductMetricService())->recordSearchResults(array_map(fn ($item): int => (int)$item->id, $items));
            $this->recordSearchLog($keyword, $items, [
                'user' => (array)($filters['user'] ?? []),
                'visitor_id' => (string)($filters['visitor_id'] ?? ''),
                'ip' => (string)($filters['ip'] ?? ''),
                'user_agent' => (string)($filters['user_agent'] ?? ''),
            ]);
        }

        return [
            'list' => array_map(fn ($item): array => $this->formatProduct($item->toArray()), $items),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'has_more' => ($page * $limit) < $total,
            ],
            'filters' => [
                'keyword' => $keyword,
                'category_id' => $categoryId,
                'kind_category_id' => $kindCategoryId,
                'sort' => $sort,
            ],
        ];
    }

    /**
     * 获取精选商品列表
     * @param string $group 分类组：'type-games' 表示按"游戏"类型分类，空则不限定
     * @param int $limit 数量上限
     */
    public function getFeaturedProducts(string $group, int $limit): array
    {
        $limit = max(1, min(50, $limit));

        $query = Product::with(['category', 'kindCategory'])
            ->where('status', 1)
            ->where('is_featured', 1)
            ->order('id', 'desc');

        // 按分类名匹配（例如：游戏、应用）
        $groupName = trim($group);
        if ($groupName !== '') {
            $matchedCategoryIds = $this->findMatchedCategoryIds($groupName, true);
            if ($matchedCategoryIds !== []) {
                $query->where(function ($q) use ($matchedCategoryIds): void {
                    $q->whereIn('category_id', $matchedCategoryIds)
                        ->whereOr('kind_category_id', 'in', $matchedCategoryIds);
                });
            }
        }

        $rows = $query->limit($limit)->select();

        return array_map(
            fn ($item): array => $this->formatProduct($item->toArray()),
            $rows->all()
        );
    }

    public function getProductDetail(int $productId): ?array
    {
        $metricService = new ProductMetricService();
        $metricReady = $metricService->isReady();
        $product = Product::with($this->productRelations($metricReady))
            ->where('status', 1)
            ->find($productId);

        if ($product === null) {
            return null;
        }

        $detail = $this->formatProduct($product->toArray());
        $detail['resources'] = $this->getProductResources($productId);
        $detail['recommend_products'] = array_map(
            fn ($item): array => $this->formatProduct($item->toArray()),
            Product::with($this->productRelations($metricReady))
                ->where('status', 1)
                ->where('id', '<>', $productId)
                ->order('id', 'desc')
                ->limit(4)
                ->select()
                ->all()
        );

        return $detail;
    }

    private function recordSearchLog(string $keyword, array $items, array $context): void
    {
        try {
            $user = (array)($context['user'] ?? []);
            $userAgent = (string)($context['user_agent'] ?? '');
            $log = ProductSearchLog::create([
                'keyword' => mb_substr($keyword, 0, 100, 'UTF-8'),
                'user_id' => (int)($user['id'] ?? 0),
                'username' => mb_substr((string)($user['username'] ?? ''), 0, 100, 'UTF-8'),
                'nickname' => mb_substr((string)($user['nickname'] ?? ''), 0, 100, 'UTF-8'),
                'visitor_id' => mb_substr((string)($context['visitor_id'] ?? ''), 0, 64, 'UTF-8'),
                'result_count' => count($items),
                'ip' => mb_substr((string)($context['ip'] ?? ''), 0, 45, 'UTF-8'),
                'device_type' => $this->detectDeviceType($userAgent),
                'user_agent' => mb_substr($userAgent, 0, 255, 'UTF-8'),
            ]);

            foreach ($items as $item) {
                $row = $item->toArray();
                ProductSearchLogItem::create([
                    'search_log_id' => (int)$log->id,
                    'keyword' => mb_substr($keyword, 0, 100, 'UTF-8'),
                    'user_id' => (int)($user['id'] ?? 0),
                    'visitor_id' => mb_substr((string)($context['visitor_id'] ?? ''), 0, 64, 'UTF-8'),
                    'product_id' => (int)($row['id'] ?? 0),
                    'product_name' => mb_substr((string)($row['name'] ?? ''), 0, 255, 'UTF-8'),
                    'product_name_en' => mb_substr((string)($row['name_en'] ?? ''), 0, 255, 'UTF-8'),
                ]);
            }
        } catch (\Throwable) {
        }
    }

    private function detectDeviceType(string $userAgent): string
    {
        $ua = strtolower($userAgent);
        if ($ua === '') {
            return 'unknown';
        }
        if (str_contains($ua, 'miniprogram') || str_contains($ua, 'micromessenger')) {
            return str_contains($ua, 'mobile') ? 'miniapp_mobile' : 'miniapp';
        }
        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
            return 'tablet';
        }
        if (str_contains($ua, 'mobile') || str_contains($ua, 'iphone') || str_contains($ua, 'android')) {
            return 'mobile';
        }
        if (str_contains($ua, 'windows') || str_contains($ua, 'macintosh') || str_contains($ua, 'linux')) {
            return 'desktop';
        }

        return 'unknown';
    }

    public function recordProductClick(int $productId): bool
    {
        if (!$this->productExists($productId)) {
            return false;
        }

        (new ProductMetricService())->recordClick($productId);
        return true;
    }

    public function productExists(int $productId): bool
    {
        if ($productId <= 0) {
            return false;
        }

        return Product::where('status', 1)->where('id', $productId)->count() > 0;
    }

    public function getProductResources(int $productId): array
    {
        $rows = CardResource::whereIn('module_type', ['download', 'tutorial'])
            ->where('status', 1)
            ->where(function ($query) use ($productId): void {
                $query->where('is_common', 1)
                    ->whereOr('product_id', $productId);
            })
            ->order('module_type', 'asc')
            ->order('is_common', 'desc')
            ->order('sort', 'asc')
            ->order('id', 'desc')
            ->select()
            ->toArray();

        $downloads = [];
        $tutorials = [];

        foreach ($rows as $row) {
            $item = [
                'id' => (int)$row['id'],
                'title' => (string)($row['title'] ?? ''),
                'is_common' => (int)($row['is_common'] ?? 0),
                'sort' => (int)($row['sort'] ?? 0),
                'remark' => (string)($row['remark'] ?? ''),
            ];

            if ((string)$row['module_type'] === 'download') {
                $item['url'] = (string)($row['url'] ?? '');
                $downloads[] = $item;
                continue;
            }

            $item['tutorial_mode'] = (string)($row['tutorial_mode'] ?? 'url');
            $item['url'] = (string)($row['url'] ?? '');
            $item['content'] = (string)($row['content'] ?? '');
            $tutorials[] = $item;
        }

        return [
            'downloads' => $downloads,
            'tutorials' => $tutorials,
        ];
    }

    public function createOrder(array $data, int $userId): CardOrder
    {
        $productId = (int)($data['product_id'] ?? 0);
        $quantity = (int)($data['quantity'] ?? 0);
        if ($userId <= 0) {
            throw new RuntimeException('请先登录后再下单');
        }

        return Db::transaction(function () use ($data, $productId, $quantity, $userId): CardOrder {
            $product = Product::where('status', 1)
                ->lock(true)
                ->find($productId);
            if ($product === null) {
                throw new RuntimeException('商品不存在');
            }

            $activeOrder = $this->findActiveOrderByProduct($userId, $productId, true);
            if ($activeOrder !== null) {
                return $activeOrder->refresh();
            }

            $energyService = new UserEnergyService();
            $user = $energyService->lockUser($userId);
            $totalAmount = (int)$product->exchange_energy * $quantity;
            $orderNo = $this->buildOrderNo();

            if ((int)$user->energy < $totalAmount) {
                throw new RuntimeException('能量不足');
            }

            $payload = [
                'order_no' => $orderNo,
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => (int)$product->exchange_energy,
                'total_amount' => $totalAmount,
                'status' => 'delivered',
                'buyer_email' => trim((string)($data['buyer_email'] ?? '')),
                'buyer_contact' => trim((string)($data['buyer_contact'] ?? '')),
                'remark' => trim((string)($data['remark'] ?? '')),
                'pay_time' => date('Y-m-d H:i:s'),
                'deliver_time' => date('Y-m-d H:i:s'),
            ];

            if ($this->hasExpiresAtColumn()) {
                $payload['expires_at'] = date('Y-m-d H:i:s', time() + 86400);
            }

            $order = CardOrder::create($payload);

            // 优先从 CardResource 获取商品绑定的账号密码或下载链接
            $deliveryContent = $this->resolveDeliveryContent($productId);

            if ($deliveryContent !== '') {
                $order->deliver_content = $deliveryContent;
                $order->save();
            } else {
                // 兼容老逻辑：从 Card 卡密池中取
                $cards = Card::where('product_id', $productId)
                    ->where('status', 'unused')
                    ->limit($quantity)
                    ->lock(true)
                    ->select();

                if ($cards->count() >= $quantity) {
                    $cardIds = [];
                    $deliveryLines = [];
                    foreach ($cards as $card) {
                        $cardIds[] = (int)$card->id;
                        $line = trim((string)$card->card_no);
                        if (trim((string)$card->card_secret) !== '') {
                            $line .= '----' . trim((string)$card->card_secret);
                        }
                        $deliveryLines[] = $line;
                    }

                    Card::whereIn('id', $cardIds)->update([
                        'status' => 'used',
                        'order_id' => (int)$order->id,
                    ]);

                    $order->deliver_content = implode("\n", $deliveryLines);
                    $order->save();
                }
            }

            $energyService->changeEnergy(
                $user,
                -$totalAmount,
                'consume',
                'order_consume',
                'Order ' . $orderNo . ' redeemed product'
            );

            (new ProductService())->syncStock($productId);
            (new ProductMetricService())->recordExchange($productId, $quantity);

            return $order->refresh();
        });
    }

    public function getOrderDetail(int $orderId): ?array
    {
        $order = CardOrder::with(['product', 'user'])->find($orderId);
        if ($order === null) {
            return null;
        }

        return $this->formatOrder($order->toArray());
    }

    /**
     * 解析商品的发货内容
     * 优先返回商品专属的账号密码资源，其次是下载链接
     * 如果商品没有专属资源，则使用通用资源
     */
    private function resolveDeliveryContent(int $productId): string
    {
        // 优先级：商品专属 account > 商品专属 download > 通用 account > 通用 download
        $resource = CardResource::where('status', 1)
            ->whereIn('module_type', ['account', 'download'])
            ->where(function ($query) use ($productId): void {
                $query->where('product_id', $productId)
                    ->whereOr('is_common', 1);
            })
            ->orderRaw("FIELD(module_type, 'account', 'download'), is_common ASC, sort ASC, id DESC")
            ->find();

        if ($resource === null) {
            return '';
        }

        $moduleType = (string)$resource->module_type;

        if ($moduleType === 'account') {
            $username = trim((string)$resource->username);
            $password = trim((string)$resource->password);
            if ($username !== '' && $password !== '') {
                return $username . '----' . $password;
            }
            if ($username !== '') {
                return $username;
            }
        }

        if ($moduleType === 'download') {
            $url = trim((string)$resource->url);
            if ($url !== '') {
                return $url;
            }
        }

        return '';
    }

    public function getOrderByNo(string $orderNo): ?array
    {
        $order = CardOrder::with(['product', 'user'])
            ->where('order_no', trim($orderNo))
            ->find();

        if ($order === null) {
            return null;
        }

        return $this->formatOrder($order->toArray());
    }

    /**
     * 查找用户对该商品最近24小时内的有效订单
     */
    public function getActiveOrderByProduct(int $userId, int $productId): ?array
    {
        if ($userId <= 0 || $productId <= 0) {
            return null;
        }

        $order = $this->findActiveOrderByProduct($userId, $productId);

        if ($order === null) {
            return null;
        }

        return $this->formatOrder($order->toArray());
    }

    private function findActiveOrderByProduct(int $userId, int $productId, bool $lock = false): ?CardOrder
    {
        $fallbackExpireTime = date('Y-m-d H:i:s', time() - 86400);

        $query = CardOrder::with(['product', 'user'])
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('status', 'delivered')
            ->order('id', 'desc');

        if ($this->hasExpiresAtColumn()) {
            $now = date('Y-m-d H:i:s');
            $query->where(function ($subQuery) use ($now, $fallbackExpireTime): void {
                $subQuery->where('expires_at', '>', $now)
                    ->whereOr(function ($orQuery) use ($fallbackExpireTime): void {
                        $orQuery->whereNull('expires_at')
                            ->where('created_at', '>=', $fallbackExpireTime);
                    });
            });
        } else {
            $query->where('created_at', '>=', $fallbackExpireTime);
        }

        if ($lock) {
            $query->lock(true);
        }

        return $query->find();
    }

    private function getRootCategories(string $groupKey): array
    {
        return Category::where('group_key', $groupKey)
            ->where('status', 1)
            ->where('parent_id', 0)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->limit(10)
            ->select()
            ->map(fn (Category $item): array => [
                'id' => (int)$item->id,
                'name' => (string)$item->name,
                'description' => (string)$item->description,
            ])
            ->all();
    }

    private function getWebsiteConfig(): array
    {
        $defaults = [
            'site_name' => '',
            'site_logo' => '',
            'site_icon' => '',
            'default_share_image' => '',
            'home_banner_image' => '',
            'customer_qr_image' => '',
            'site_tagline' => '',
            'site_intro' => '',
            'service_notice' => '',
            'record_number' => '',
            'copyright_text' => '',
            'contact_email' => '',
            'seo_keywords' => '',
            'seo_description' => '',
        ];

        $rows = SystemConfig::where('group_key', 'website')->select();
        foreach ($rows as $row) {
            $configKey = (string)$row->config_key;
            if (!array_key_exists($configKey, $defaults)) {
                continue;
            }

            $defaults[$configKey] = trim((string)$row->config_value);
        }

        return $defaults;
    }

    private function getCategoryTree(string $groupKey): array
    {
        $rows = Category::where('group_key', $groupKey)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return (new CategoryService())->buildTree($rows);
    }

    private function applyProductKeywordFilter($query, string $keyword, string $matchFields = ''): void
    {
        // 仅匹配指定字段（用于游戏搜索：只匹配 name 和 name_en）
        $allowedFields = ['name', 'name_en', 'description', 'game_size', 'supported_languages', 'compatibility'];
        $defaultFieldsString = 'name|name_en|description|game_size|supported_languages|compatibility';

        $useDefaultFields = true;
        $fieldsString = $defaultFieldsString;

        if ($matchFields !== '') {
            $requested = array_filter(array_map('trim', explode(',', $matchFields)));
            $valid = array_values(array_intersect($requested, $allowedFields));
            if ($valid !== []) {
                $fieldsString = implode('|', $valid);
                $useDefaultFields = false;
            }
        }

        $matchedCategoryIds = $useDefaultFields ? $this->findMatchedCategoryIds($keyword, true) : [];
        $isNumericKeyword = ctype_digit($keyword);

        $query->where(function ($subQuery) use ($keyword, $fieldsString, $matchedCategoryIds, $isNumericKeyword, $useDefaultFields): void {
            $subQuery->whereLike($fieldsString, '%' . $keyword . '%');

            if ($useDefaultFields && $matchedCategoryIds !== []) {
                $subQuery->whereOr(function ($orQuery) use ($matchedCategoryIds): void {
                    $orQuery->whereIn('category_id', $matchedCategoryIds)
                        ->whereOr('kind_category_id', 'in', $matchedCategoryIds);
                });
            }

            if ($useDefaultFields && $isNumericKeyword) {
                $subQuery->whereOr('id', (int)$keyword);
            }
        });
    }

    /** 解析"游戏"类型分类及其子分类 ID（用于首页只展示游戏内容） */
    private function resolveGameCategoryIds(): array
    {
        $candidates = ['游戏', '游戏列表', 'games'];
        $categoryService = new CategoryService();
        $matched = null;

        foreach ($candidates as $name) {
            $category = Category::where('group_key', CategoryService::GROUP_TYPE)
                ->where('name', $name)
                ->where('status', 1)
                ->find();
            if ($category !== null) {
                $matched = $category;
                break;
            }
        }

        if ($matched === null) {
            $matched = Category::where('group_key', CategoryService::GROUP_TYPE)
                ->where('status', 1)
                ->whereLike('name', '%游戏%')
                ->find();
        }

        if ($matched === null) {
            return [];
        }

        $rootId = (int)$matched->id;
        $ids = array_values(array_unique(array_merge(
            [$rootId],
            $categoryService->getDescendantIds($rootId)
        )));
        return array_map('intval', $ids);
    }

    private function findMatchedCategoryIds(string $keyword, bool $onlyActive = false): array
    {
        $categoryQuery = Category::whereLike('name|description', '%' . $keyword . '%');
        if ($onlyActive) {
            $categoryQuery->where('status', 1);
        }

        $matchedRows = $categoryQuery->column('id');
        if (!is_array($matchedRows) || $matchedRows === []) {
            return [];
        }

        $categoryService = new CategoryService();
        $result = [];

        foreach ($matchedRows as $matchedId) {
            $categoryId = (int)$matchedId;
            if ($categoryId <= 0) {
                continue;
            }

            $result[] = $categoryId;
            $result = array_merge($result, $categoryService->getDescendantIds($categoryId));
        }

        return array_values(array_unique(array_map('intval', $result)));
    }

    private function productRelations(bool $includeMetric = true): array
    {
        $relations = ['category', 'kindCategory'];
        if ($includeMetric) {
            $relations[] = 'metric';
        }

        return $relations;
    }

    private function formatProduct(array $row): array
    {
        $galleryImages = is_array($row['gallery_images'] ?? null)
            ? array_values(array_filter(array_map(static fn ($item): string => trim((string)$item), $row['gallery_images'])))
            : [];
        $coverImage = trim((string)($row['cover_image'] ?? ''));
        $images = [];

        if ($coverImage !== '') {
            $images[] = $coverImage;
        }

        foreach ($galleryImages as $image) {
            if ($image === '' || in_array($image, $images, true)) {
                continue;
            }
            $images[] = $image;
        }

        $row['cover_image'] = $coverImage;
        $row['gallery_images'] = $galleryImages;
        $row['images'] = $images;
        $row['display_image'] = $coverImage !== '' ? $coverImage : ($galleryImages[0] ?? '');
        $row['exchange_energy'] = (int)($row['exchange_energy'] ?? 0);
        $row['stock'] = (int)($row['stock'] ?? 0);
        $row['status'] = (int)($row['status'] ?? 0);
        $metric = is_array($row['metric'] ?? null) ? $row['metric'] : [];
        $row['click_count'] = (int)($metric['click_count'] ?? 0);
        $row['exchange_count'] = (int)($metric['exchange_count'] ?? 0);
        $category = is_array($row['category'] ?? null) ? $row['category'] : [];
        $kindCategory = is_array($row['kind_category'] ?? null) ? $row['kind_category'] : [];
        $row['category_id'] = (int)($row['category_id'] ?? ($category['id'] ?? 0));
        $row['category_name'] = (string)($category['name'] ?? '');
        $row['kind_category_id'] = (int)($row['kind_category_id'] ?? ($kindCategory['id'] ?? 0));
        $row['kind_category_name'] = (string)($kindCategory['name'] ?? '');

        return $row;
    }

    private function formatOrder(array $row): array
    {
        $status = $this->resolveOrderStatus($row);
        $row['status'] = $status;
        $statusMap = [
            'pending' => '待处理',
            'paid' => '已支付',
            'delivered' => '已发货',
            'cancelled' => '已取消',
            'refunded' => '已退款',
            'expired' => '已失效',
        ];

        $row['status_text'] = $statusMap[$status] ?? $status;
        $row['quantity'] = (int)($row['quantity'] ?? 0);
        $row['unit_price'] = (int)round((float)($row['unit_price'] ?? 0));
        $row['total_amount'] = (int)round((float)($row['total_amount'] ?? 0));
        $row['user_id'] = (int)($row['user_id'] ?? 0);
        $row['expires_at'] = $this->normalizeOrderExpiresAt($row);

        if (!empty($row['product']) && is_array($row['product'])) {
            $row['product'] = $this->formatProduct($row['product']);
        }

        if ($status !== 'delivered') {
            $row['deliver_content'] = '';
        }

        return $row;
    }

    private function resolveOrderStatus(array $row): string
    {
        $status = (string)($row['status'] ?? 'pending');
        if (!in_array($status, ['pending', 'paid', 'delivered'], true)) {
            return $status;
        }

        $expiresAt = trim((string)($row['expires_at'] ?? ''));
        if ($expiresAt !== '' && strtotime($expiresAt) <= time()) {
            return 'expired';
        }

        if ($expiresAt === '') {
            $createdAt = trim((string)($row['created_at'] ?? ''));
            if ($createdAt !== '' && strtotime($createdAt) + 86400 <= time()) {
                return 'expired';
            }
        }

        return $status;
    }

    private function normalizeOrderExpiresAt(array $row): string
    {
        $expiresAt = trim((string)($row['expires_at'] ?? ''));
        if ($expiresAt !== '') {
            return $expiresAt;
        }

        $createdAt = trim((string)($row['created_at'] ?? ''));
        $createdTime = strtotime($createdAt);
        if ($createdTime <= 0) {
            return '';
        }

        return date('Y-m-d H:i:s', $createdTime + 86400);
    }

    private function hasExpiresAtColumn(): bool
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }

        $rows = Db::query("SHOW COLUMNS FROM `card_orders` LIKE 'expires_at'");
        $exists = !empty($rows);

        return $exists;
    }

    private function buildOrderNo(): string
    {
        return 'FK' . date('YmdHis') . random_int(1000, 9999);
    }
}
