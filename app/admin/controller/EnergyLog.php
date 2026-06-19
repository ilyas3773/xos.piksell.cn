<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\model\EnergyLog as EnergyLogModel;

class EnergyLog extends BaseController
{
    public function index(): \think\Response
    {
        $page = max(1, (int)$this->request->get('page/d', 1));
        $limit = max(1, min(500, (int)$this->request->get('limit/d', 20)));
        $userId = (int)$this->request->get('user_id/d', 0);

        $query = EnergyLogModel::with(['user', 'operator'])
            ->order('id', 'desc');
        if ($userId > 0) {
            $query->where('user_id', $userId);
        }

        $total = (clone $query)->count();
        $list = $query->page($page, $limit)->select();

        return $this->success([
            'list' => $list,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
            ],
        ]);
    }
}
