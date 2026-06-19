<?php
declare(strict_types=1);

namespace app\index\controller;

class BaseController extends \app\BaseController
{
    protected function success(array|\JsonSerializable $data = [], string $msg = 'success', int $code = 0): \think\Response
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    protected function error(string $msg = 'error', int $code = 1, int $httpStatus = 200, array $data = []): \think\Response
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ], $httpStatus);
    }
}
