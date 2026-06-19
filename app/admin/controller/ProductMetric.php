<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\service\ProductMetricService;
use RuntimeException;

class ProductMetric extends BaseController
{
    public function index(): \think\Response
    {
        return $this->success((new ProductMetricService())->list([
            'page' => (int)$this->request->get('page/d', 1),
            'limit' => (int)$this->request->get('limit/d', 20),
            'keyword' => trim((string)$this->request->get('keyword', '')),
            'sort' => trim((string)$this->request->get('sort', 'exchange_count')),
        ]));
    }

    public function backfill(): \think\Response
    {
        try {
            $count = (new ProductMetricService())->backfillFromOrders();
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success([
            'count' => $count,
        ], '统计数据同步完成');
    }
}
