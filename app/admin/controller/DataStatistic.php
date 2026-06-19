<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\DataStatisticService;

class DataStatistic extends BaseController
{
    public function index(): \think\Response
    {
        return $this->success((new DataStatisticService())->rankings(
            (int)$this->request->get('limit/d', 20),
            trim((string)$this->request->get('keyword', '')),
            trim((string)$this->request->get('period', 'day'))
        ));
    }

    public function exchangeRanking(): \think\Response
    {
        return $this->success((new DataStatisticService())->exchangeRanking(
            (int)$this->request->get('limit/d', 20),
            trim((string)$this->request->get('keyword', '')),
            trim((string)$this->request->get('period', 'day'))
        ));
    }

    public function clickRanking(): \think\Response
    {
        return $this->success((new DataStatisticService())->clickRanking(
            (int)$this->request->get('limit/d', 20),
            trim((string)$this->request->get('keyword', '')),
            trim((string)$this->request->get('period', 'day'))
        ));
    }

    public function searchRanking(): \think\Response
    {
        return $this->success((new DataStatisticService())->searchRanking(
            (int)$this->request->get('limit/d', 20),
            trim((string)$this->request->get('keyword', '')),
            trim((string)$this->request->get('period', 'day'))
        ));
    }
}
