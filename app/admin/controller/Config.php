<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\ConfigService;
use InvalidArgumentException;

class Config extends BaseController
{
    public function index(): \think\Response
    {
        $group = trim((string)$this->request->get('group', ConfigService::GROUP_MINIAPP));

        try {
            $data = (new ConfigService())->getGroup($group);
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($data);
    }

    public function saveGroup(string $group): \think\Response
    {
        $data = $this->request->post();
        if (empty($data)) {
            $data = $this->request->put();
        }

        try {
            $result = (new ConfigService())->saveGroup($group, $data);
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($result, '系统配置保存成功');
    }
}
