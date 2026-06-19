<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\model\ProductSearchLog as ProductSearchLogModel;
use think\facade\Db;

class ProductSearchLog extends BaseController
{
    public function index(): \think\Response
    {
        $page = max(1, (int)$this->request->get('page/d', 1));
        $limit = max(1, min(500, (int)$this->request->get('limit/d', 20)));
        $keyword = trim((string)$this->request->get('keyword', ''));
        $userKeyword = trim((string)$this->request->get('user_keyword', ''));
        $visitorId = trim((string)$this->request->get('visitor_id', ''));
        $startDate = trim((string)$this->request->get('start_date', ''));
        $endDate = trim((string)$this->request->get('end_date', ''));
        $period = trim((string)$this->request->get('period', 'day'));

        $query = ProductSearchLogModel::order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('keyword', '%' . $keyword . '%');
        }
        if ($userKeyword !== '') {
            $query->whereLike('username|nickname', '%' . $userKeyword . '%');
        }
        if ($visitorId !== '') {
            $query->whereLike('visitor_id', '%' . $visitorId . '%');
        }
        if ($startDate !== '') {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate !== '') {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        $total = (clone $query)->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success([
            'list' => $list,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
            ],
            'summary' => [
                'total' => ProductSearchLogModel::count(),
                'today_total' => ProductSearchLogModel::where('created_at', '>=', date('Y-m-d 00:00:00'))->count(),
                'user_total' => ProductSearchLogModel::where('user_id', '>', 0)->count(),
                'guest_total' => ProductSearchLogModel::where('user_id', 0)->count(),
            ],
            'analysis' => $this->analysis($period),
        ]);
    }

    public function analysisData(): \think\Response
    {
        return $this->success($this->analysis(trim((string)$this->request->get('period', 'day'))));
    }

    private function analysis(string $period): array
    {
        $period = $this->normalizePeriod($period);
        $range = $this->resolveDateRange($period);

        return [
            'period' => $period,
            'start_date' => $range['start_date'],
            'end_date' => $range['end_date'],
            'hot_keywords' => $this->hotKeywords($range, false),
            'zero_result_keywords' => $this->hotKeywords($range, true),
            'hot_products' => $this->hotProducts($range),
        ];
    }

    private function hotKeywords(array $range, bool $zeroOnly): array
    {
        $query = Db::table('product_search_logs')
            ->field('keyword, COUNT(*) AS search_count, SUM(result_count) AS result_total, AVG(result_count) AS avg_result_count, MAX(created_at) AS last_search_at')
            ->whereBetween('created_at', [$range['start_at'], $range['end_at']])
            ->group('keyword')
            ->order('search_count', 'desc')
            ->order('last_search_at', 'desc')
            ->limit(20);
        if ($zeroOnly) {
            $query->where('result_count', 0);
        }

        $rows = $query->select()->toArray();

        return array_map(function (array $row): array {
            return [
                'keyword' => (string)($row['keyword'] ?? ''),
                'search_count' => (int)($row['search_count'] ?? 0),
                'result_total' => (int)($row['result_total'] ?? 0),
                'avg_result_count' => round((float)($row['avg_result_count'] ?? 0), 2),
                'last_search_at' => (string)($row['last_search_at'] ?? ''),
            ];
        }, $rows);
    }

    private function hotProducts(array $range): array
    {
        $rows = Db::table('product_search_log_items')
            ->field('product_id, product_name, product_name_en, COUNT(*) AS hit_count, COUNT(DISTINCT search_log_id) AS search_count, MAX(created_at) AS last_hit_at')
            ->whereBetween('created_at', [$range['start_at'], $range['end_at']])
            ->group('product_id, product_name, product_name_en')
            ->order('hit_count', 'desc')
            ->order('last_hit_at', 'desc')
            ->limit(20)
            ->select()
            ->toArray();

        return array_map(function (array $row): array {
            return [
                'product_id' => (int)($row['product_id'] ?? 0),
                'product_name' => (string)($row['product_name'] ?? ''),
                'product_name_en' => (string)($row['product_name_en'] ?? ''),
                'hit_count' => (int)($row['hit_count'] ?? 0),
                'search_count' => (int)($row['search_count'] ?? 0),
                'last_hit_at' => (string)($row['last_hit_at'] ?? ''),
            ];
        }, $rows);
    }

    private function normalizePeriod(string $period): string
    {
        return in_array($period, ['day', 'week', 'month', 'quarter', 'year', 'all'], true) ? $period : 'day';
    }

    private function resolveDateRange(string $period): array
    {
        $period = $this->normalizePeriod($period);
        if ($period === 'week') {
            $start = date('Y-m-d', strtotime('monday this week'));
        } elseif ($period === 'month') {
            $start = date('Y-m-01');
        } elseif ($period === 'quarter') {
            $month = (int)date('n');
            $quarterStartMonth = (int)(floor(($month - 1) / 3) * 3 + 1);
            $start = date('Y-') . str_pad((string)$quarterStartMonth, 2, '0', STR_PAD_LEFT) . '-01';
        } elseif ($period === 'year') {
            $start = date('Y-01-01');
        } elseif ($period === 'all') {
            $start = '1970-01-01';
        } else {
            $start = date('Y-m-d');
        }
        $end = date('Y-m-d');

        return [
            'start_date' => $start,
            'end_date' => $end,
            'start_at' => $start . ' 00:00:00',
            'end_at' => $end . ' 23:59:59',
        ];
    }
}
