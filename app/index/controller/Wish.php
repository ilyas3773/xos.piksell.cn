<?php
declare(strict_types=1);

namespace app\index\controller;

use app\model\ProductWish;
use app\model\SystemConfig;

class Wish extends BaseController
{
    public function submit(): \think\Response
    {
        $name = trim((string)$this->request->post('name', ''));
        if ($name === '') {
            return $this->error('请填写想要的游戏 / 应用名称');
        }
        if (mb_strlen($name) > 100) {
            return $this->error('名称长度不能超过100字符');
        }

        $description = trim((string)$this->request->post('description', ''));
        if (mb_strlen($description) > 500) {
            return $this->error('描述长度不能超过500字符');
        }

        $contact = trim((string)$this->request->post('contact', ''));
        if (mb_strlen($contact) > 100) {
            return $this->error('联系方式长度不能超过100字符');
        }

        $userId = (int)(($this->request->user ?? [])['id'] ?? 0);

        $wish = ProductWish::create([
            'user_id' => $userId,
            'name' => $name,
            'description' => $description,
            'contact' => $contact,
            'status' => 0,
        ]);

        return $this->success([
            'id' => (int)$wish->id,
        ], '许愿成功，我们会尽快处理');
    }

    /**
     * 获取客服联系方式
     */
    public function customerContact(): \think\Response
    {
        $contact = [
            'name' => '',
            'wechat' => '',
            'qq' => '',
            'phone' => '',
            'email' => '',
            'qr_image' => '',
            'work_time' => '',
        ];

        // service 配置组：客服基本信息
        $rows = SystemConfig::where('group_key', 'service')->select();
        foreach ($rows as $row) {
            $key = (string)$row->config_key;
            $value = trim((string)$row->config_value);
            if ($key === 'service_name') $contact['name'] = $value;
            elseif ($key === 'wechat') $contact['wechat'] = $value;
            elseif ($key === 'qq') $contact['qq'] = $value;
            elseif ($key === 'phone') $contact['phone'] = $value;
            elseif ($key === 'email') $contact['email'] = $value;
            elseif ($key === 'work_time') $contact['work_time'] = $value;
        }

        // website 配置组：客服二维码 + 兜底邮箱
        $websiteRows = SystemConfig::where('group_key', 'website')->select();
        foreach ($websiteRows as $row) {
            $key = (string)$row->config_key;
            $value = trim((string)$row->config_value);
            if ($key === 'customer_qr_image' && $contact['qr_image'] === '') {
                $contact['qr_image'] = $value;
            }
            if ($key === 'contact_email' && $contact['email'] === '') {
                $contact['email'] = $value;
            }
        }

        return $this->success($contact);
    }
}
