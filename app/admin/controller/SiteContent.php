<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\SiteContentService;
use app\admin\validate\SiteContentValidate;
use InvalidArgumentException;

class SiteContent extends BaseController
{
    public function read(string $key): \think\Response
    {
        try {
            $data = (new SiteContentService())->getContent($key);
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($data);
    }

    public function update(string $key): \think\Response
    {
        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $this->validate($data, SiteContentValidate::class);

        try {
            $result = (new SiteContentService())->saveContent($key, $data);
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($result, '内容保存成功');
    }
}
