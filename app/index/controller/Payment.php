<?php
declare(strict_types=1);

namespace app\index\controller;

use app\index\service\WeChatPayService;
use app\index\service\EpayService;
use RuntimeException;

class Payment extends BaseController
{
    public function wechatEnergyRechargeNotify(): \think\Response
    {
        try {
            $result = (new WeChatPayService())->handleEnergyRechargeNotify((string)$this->request->getContent());
        } catch (RuntimeException $exception) {
            return json([
                'code' => 'FAIL',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return json($result);
    }

    public function epayEnergyRechargeNotify(): \think\Response
    {
        app()->config->set([
            'trace' => false,
            'show_error_msg' => false,
        ], 'app');

        if (empty($this->request->param())) {
            return response('success');
        }

        try {
            $result = (new EpayService())->handleEnergyRechargeNotify($this->request->param());
        } catch (RuntimeException $exception) {
            return response('fail:' . $exception->getMessage(), 500);
        }

        return response($result);
    }

    public function epayEnergyRechargeReturn(): \think\Response
    {
        app()->config->set([
            'trace' => false,
        ], 'app');

        return redirect('/is/index.html#/pages/energy/index');
    }
}
