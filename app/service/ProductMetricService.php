<?php
declare(strict_types=1);

namespace app\service;

use app\model\Product;
use app\model\ProductMetric;
use app\model\ProductMetricDaily;
use think\facade\Db;

class ProductMetricService
{
    public function isReady(): bool
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }

        try {
            $ready = !empty(Db::query("SHOW TABLES LIKE 'product_metrics'"));
        } catch (\Throwable) {
            $ready = false;
        }

        return $ready;
    }

    public function isDailyReady(): bool
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }

        try {
            $ready = !empty(Db::query("SHOW TABLES LIKE 'product_metric_daily'"));
        } catch (\Throwable) {
            $ready = false;
        }

        return $ready;
    }

    public function recordClick(int $productId): void
    {
        if ($productId <= 0) {
            return;
        }

        if ($this->isReady()) {
            $this->increment($productId, 'click_count', 1);
        }
        $this->incrementDaily($productId, 'click_count', 1);
    }

    public function recordExchange(int $productId, int $quantity = 1): void
    {
        if ($productId <= 0) {
            return;
        }

        $amount = max(1, $quantity);
        if ($this->isReady()) {
            $this->increment($productId, 'exchange_count', $amount);
        }
        $this->incrementDaily($productId, 'exchange_count', $amount);
    }

    public function recordSearchResults(array $productIds): void
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds), fn (int $id): bool => $id > 0)));
        if ($productIds === []) {
            return;
        }

        foreach ($productIds as $productId) {
            $this->incrementDaily($productId, 'search_count', 1);
        }
    }

    public function list(array $filters = []): array
    {
        if (!$this->isReady()) {
            return [
                'list' => [],
                'pagination' => [
                    'page' => 1,
                    'limit' => 20,
                    'total' => 0,
                ],
                'filters' => [
                    'keyword' => '',
                    'sort' => 'exchange_count',
                ],
            ];
        }

        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = max(1, min(500, (int)($filters['limit'] ?? 20)));
        $keyword = trim((string)($filters['keyword'] ?? ''));
        $sort = trim((string)($filters['sort'] ?? 'exchange_count'));

        $allowedSorts = ['click_count', 'exchange_count', 'id', 'updated_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'exchange_count';
        }

        $query = ProductMetric::with(['product'])->order($sort, 'desc')->order('id', 'desc');
        if ($keyword !== '') {
            $productIds = Product::whereLike('name|name_en', '%' . $keyword . '%')->column('id');
            if (empty($productIds)) {
                $query->where('product_id', 0);
            } else {
                $query->whereIn('product_id', array_map('intval', $productIds));
            }
        }

        $total = (clone $query)->count();
        $rows = $query->page($page, $limit)->select();

        return [
            'list' => array_map(fn ($item): array => $this->formatMetric($item->toArray()), $rows->all()),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
            ],
            'filters' => [
                'keyword' => $keyword,
                'sort' => $sort,
            ],
        ];
    }

    public function backfillFromOrders(): int
    {
        if (!$this->isReady()) {
            throw new \RuntimeException('请先执行 database/product_metric_init.sql 创建商品统计表');
        }

        $rows = Db::table('card_orders')
            ->field('product_id, SUM(quantity) AS total_quantity')
            ->where('status', 'delivered')
            ->group('product_id')
            ->select();

        $count = 0;
        foreach ($rows as $row) {
            $productId = (int)($row['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $this->ensureMetric($productId)->save([
                'exchange_count' => (int)($row['total_quantity'] ?? 0),
            ]);
            $count++;
        }

        return $count;
    }

    public function ensureMetric(int $productId): ProductMetric
    {
        if (!$this->isReady()) {
            throw new \RuntimeException('请先执行 database/product_metric_init.sql 创建商品统计表');
        }

        $metric = ProductMetric::where('product_id', $productId)->find();
        if ($metric !== null) {
            return $metric;
        }

        try {
            return ProductMetric::create([
                'product_id' => $productId,
                'click_count' => 0,
                'exchange_count' => 0,
            ]);
        } catch (\Throwable) {
            $metric = ProductMetric::where('product_id', $productId)->find();
            if ($metric !== null) {
                return $metric;
            }

            throw new \RuntimeException('商品统计记录创建失败');
        }
    }

    private function increment(int $productId, string $field, int $amount): void
    {
        $this->ensureMetric($productId);
        ProductMetric::where('product_id', $productId)->update([
            $field => Db::raw($field . ' + ' . $amount),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function incrementDaily(int $productId, string $field, int $amount): void
    {
        if (!$this->isDailyReady()) {
            return;
        }

        $date = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        try {
            ProductMetricDaily::create([
                'stat_date' => $date,
                'product_id' => $productId,
                'click_count' => $field === 'click_count' ? $amount : 0,
                'exchange_count' => $field === 'exchange_count' ? $amount : 0,
                'search_count' => $field === 'search_count' ? $amount : 0,
            ]);
        } catch (\Throwable) {
            ProductMetricDaily::where('stat_date', $date)
                ->where('product_id', $productId)
                ->update([
                    $field => Db::raw($field . ' + ' . $amount),
                    'updated_at' => $now,
                ]);
        }
    }

    private function formatMetric(array $row): array
    {
        $product = $row['product'] ?? null;

        return [
            'id' => (int)($row['id'] ?? 0),
            'product_id' => (int)($row['product_id'] ?? 0),
            'click_count' => (int)($row['click_count'] ?? 0),
            'exchange_count' => (int)($row['exchange_count'] ?? 0),
            'product_name' => is_array($product) ? (string)($product['name'] ?? '') : '',
            'product_name_en' => is_array($product) ? (string)($product['name_en'] ?? '') : '',
            'product_status' => is_array($product) ? (int)($product['status'] ?? 0) : 0,
            'created_at' => (string)($row['created_at'] ?? ''),
            'updated_at' => (string)($row['updated_at'] ?? ''),
        ];
    }
}
