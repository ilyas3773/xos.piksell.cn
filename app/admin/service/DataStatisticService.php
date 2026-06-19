<?php
declare(strict_types=1);

namespace app\admin\service;

use app\model\ProductMetricDaily;
use app\service\ProductMetricService;
use think\facade\Db;

class DataStatisticService
{
    public function rankings(int $limit = 20, string $keyword = '', string $period = 'day'): array
    {
        return [
            'enabled' => $this->metricReady(),
            'daily_enabled' => $this->dailyReady(),
            'period' => $this->normalizePeriod($period),
            'summary' => $this->summary($period),
            'exchange_ranking' => $this->exchangeRanking($limit, $keyword, $period),
            'click_ranking' => $this->clickRanking($limit, $keyword, $period),
            'search_ranking' => $this->searchRanking($limit, $keyword, $period),
        ];
    }

    public function exchangeRanking(int $limit = 20, string $keyword = '', string $period = 'day'): array
    {
        return $this->orderExchangeRanking($limit, $keyword, $period);
    }

    public function clickRanking(int $limit = 20, string $keyword = '', string $period = 'day'): array
    {
        return $this->dailyRanking('click', $limit, $keyword, $period);
    }

    public function searchRanking(int $limit = 20, string $keyword = '', string $period = 'day'): array
    {
        return $this->dailyRanking('search', $limit, $keyword, $period);
    }

    private function dailyRanking(string $type, int $limit, string $keyword, string $period): array
    {
        $limit = max(1, min(100, $limit));
        $keyword = trim($keyword);
        $field = $type === 'search' ? 'search_count' : 'click_count';
        $period = $this->normalizePeriod($period);
        $range = $this->resolveDateRange($period);
        $label = $this->periodLabel($period) . ($type === 'search' ? '搜索排行榜' : '点击排行榜');

        if (!$this->dailyReady()) {
            return $this->emptyRanking($type, $period, $range, $label, $field, false);
        }

        $query = ProductMetricDaily::alias('m')
            ->leftJoin('products p', 'p.id = m.product_id')
            ->field('m.product_id, SUM(m.click_count) AS click_count, 0 AS exchange_count, SUM(m.search_count) AS search_count, MAX(m.updated_at) AS updated_at, p.name AS product_name, p.name_en AS product_name_en, p.status AS product_status, p.cover_image AS cover_image')
            ->whereBetween('m.stat_date', [$range['start_date'], $range['end_date']])
            ->group('m.product_id')
            ->having('SUM(m.' . $field . ') > 0')
            ->order($field, 'desc')
            ->order('m.product_id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('p.name|p.name_en', '%' . $keyword . '%');
        }

        return $this->rankingResult($query, $type, $period, $range, $label, $field, $limit);
    }

    private function orderExchangeRanking(int $limit, string $keyword, string $period): array
    {
        $limit = max(1, min(100, $limit));
        $keyword = trim($keyword);
        $period = $this->normalizePeriod($period);
        $range = $this->resolveDateRange($period);
        $label = $this->periodLabel($period) . '兑换排行榜';
        $field = 'exchange_count';

        $query = Db::table('card_orders')->alias('o')
            ->leftJoin('products p', 'p.id = o.product_id')
            ->field("MIN(o.product_id) AS product_id, 0 AS click_count, SUM(o.quantity) AS exchange_count, 0 AS search_count, MAX(o.updated_at) AS updated_at, COALESCE(NULLIF(p.name, ''), CONCAT('商品#', MIN(o.product_id))) AS product_name, MAX(p.name_en) AS product_name_en, MAX(p.status) AS product_status, MAX(p.cover_image) AS cover_image")
            ->where('o.status', 'delivered')
            ->whereRaw('DATE(COALESCE(o.deliver_time, o.updated_at, o.created_at)) BETWEEN ? AND ?', [$range['start_date'], $range['end_date']])
            ->group('COALESCE(NULLIF(p.name, \'\'), CONCAT(\'商品#\', o.product_id))')
            ->having('SUM(o.quantity) > 0')
            ->order($field, 'desc')
            ->order('product_id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('p.name|p.name_en', '%' . $keyword . '%');
        }

        return $this->rankingResult($query, 'exchange', $period, $range, $label, $field, $limit);
    }

    private function rankingResult($query, string $type, string $period, array $range, string $label, string $field, int $limit): array
    {
        $total = (clone $query)->count();
        $rows = $query->limit($limit)->select()->toArray();
        $list = [];

        foreach ($rows as $index => $row) {
            $list[] = $this->formatRankingItem($row, $field, $index + 1);
        }

        return [
            'type' => $type,
            'period' => $period,
            'start_date' => $range['start_date'],
            'end_date' => $range['end_date'],
            'label' => $label,
            'field' => $field,
            'list' => $list,
            'total' => $total,
            'enabled' => true,
        ];
    }

    private function emptyRanking(string $type, string $period, array $range, string $label, string $field, bool $enabled): array
    {
        return [
            'type' => $type,
            'period' => $period,
            'start_date' => $range['start_date'],
            'end_date' => $range['end_date'],
            'label' => $label,
            'field' => $field,
            'list' => [],
            'total' => 0,
            'enabled' => $enabled,
        ];
    }

    private function summary(string $period): array
    {
        $range = $this->resolveDateRange($period);
        $clickQuery = ProductMetricDaily::whereBetween('stat_date', [$range['start_date'], $range['end_date']]);
        $exchangeQuery = Db::table('card_orders')
            ->where('status', 'delivered')
            ->whereRaw('DATE(COALESCE(deliver_time, updated_at, created_at)) BETWEEN ? AND ?', [$range['start_date'], $range['end_date']]);

        if (!$this->dailyReady()) {
            return [
                'product_count' => 0,
                'total_click_count' => 0,
                'total_exchange_count' => (int)(clone $exchangeQuery)->sum('quantity'),
                'total_search_count' => 0,
            ];
        }

        return [
            'product_count' => (int)(clone $clickQuery)->distinct(true)->count('product_id'),
            'total_click_count' => (int)(clone $clickQuery)->sum('click_count'),
            'total_exchange_count' => (int)(clone $exchangeQuery)->sum('quantity'),
            'total_search_count' => (int)(clone $clickQuery)->sum('search_count'),
        ];
    }

    private function formatRankingItem(array $row, string $field, int $rank): array
    {
        return [
            'rank' => $rank,
            'product_id' => (int)($row['product_id'] ?? 0),
            'product_name' => (string)($row['product_name'] ?? ''),
            'product_name_en' => (string)($row['product_name_en'] ?? ''),
            'product_status' => (int)($row['product_status'] ?? 0),
            'cover_image' => (string)($row['cover_image'] ?? ''),
            'click_count' => (int)($row['click_count'] ?? 0),
            'exchange_count' => (int)($row['exchange_count'] ?? 0),
            'search_count' => (int)($row['search_count'] ?? 0),
            'value' => (int)($row[$field] ?? 0),
            'updated_at' => (string)($row['updated_at'] ?? ''),
        ];
    }

    private function normalizePeriod(string $period): string
    {
        return in_array($period, ['day', 'week', 'month', 'quarter', 'year', 'all'], true) ? $period : 'day';
    }

    private function resolveDateRange(string $period): array
    {
        $period = $this->normalizePeriod($period);
        if ($period === 'week') {
            return [
                'start_date' => date('Y-m-d', strtotime('monday this week')),
                'end_date' => date('Y-m-d'),
            ];
        }

        if ($period === 'month') {
            return [
                'start_date' => date('Y-m-01'),
                'end_date' => date('Y-m-d'),
            ];
        }

        if ($period === 'quarter') {
            $month = (int)date('n');
            $quarterStartMonth = (int)(floor(($month - 1) / 3) * 3 + 1);
            return [
                'start_date' => date('Y-') . str_pad((string)$quarterStartMonth, 2, '0', STR_PAD_LEFT) . '-01',
                'end_date' => date('Y-m-d'),
            ];
        }

        if ($period === 'year') {
            return [
                'start_date' => date('Y-01-01'),
                'end_date' => date('Y-m-d'),
            ];
        }

        if ($period === 'all') {
            return [
                'start_date' => '1970-01-01',
                'end_date' => date('Y-m-d'),
            ];
        }

        return [
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d'),
        ];
    }

    private function periodLabel(string $period): string
    {
        return match ($this->normalizePeriod($period)) {
            'week' => '本周',
            'month' => '本月',
            'quarter' => '本季度',
            'year' => '本年',
            'all' => '全部时间',
            default => '今日',
        };
    }

    private function metricReady(): bool
    {
        return (new ProductMetricService())->isReady();
    }

    private function dailyReady(): bool
    {
        return (new ProductMetricService())->isDailyReady();
    }
}
