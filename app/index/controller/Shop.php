<?php
declare(strict_types=1);

namespace app\index\controller;

use app\index\service\ShopService;
use app\index\service\UserTokenService;
use app\index\validate\OrderCreateValidate;
use RuntimeException;

class Shop extends BaseController
{
    public function home(): \think\Response
    {
        return $this->success((new ShopService())->getHome());
    }

    public function categories(): \think\Response
    {
        $group = trim((string)$this->request->get('group', 'all'));
        return $this->success((new ShopService())->getCategories($group));
    }

    public function faqs(): \think\Response
    {
        return $this->success((new ShopService())->getFaqs());
    }

    public function products(): \think\Response
    {
        return $this->success((new ShopService())->getProducts([
            'page' => (int)$this->request->get('page/d', 1),
            'limit' => (int)$this->request->get('limit/d', 12),
            'keyword' => trim((string)$this->request->get('keyword', '')),
            'category_id' => (int)$this->request->get('category_id/d', 0),
            'kind_category_id' => (int)$this->request->get('kind_category_id/d', 0),
            'sort' => trim((string)$this->request->get('sort', 'latest')),
            'is_featured' => (int)$this->request->get('is_featured/d', 0),
            'match_fields' => trim((string)$this->request->get('match_fields', '')),
            'user' => $this->optionalUser(),
            'visitor_id' => trim((string)$this->request->header('X-Visitor-Id', '')),
            'ip' => $this->request->ip(),
            'user_agent' => trim((string)$this->request->header('User-Agent', '')),
        ]));
    }

    /**
     * 获取精选商品（用于游戏页轮播图等场景）
     */
    public function featuredProducts(): \think\Response
    {
        $group = trim((string)$this->request->get('group', ''));
        $limit = max(1, min(50, (int)$this->request->get('limit/d', 20)));
        return $this->success((new ShopService())->getFeaturedProducts($group, $limit));
    }

    private function optionalUser(): array
    {
        $token = (string)$this->request->header('Authorization', '');
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }
        if ($token === '') {
            return [];
        }

        try {
            return UserTokenService::parseToken($token);
        } catch (\Throwable) {
            return [];
        }
    }

    public function readProduct(int $id = 0): \think\Response
    {
        $id = $id > 0 ? $id : (int)$this->request->get('id/d', 0);
        $detail = (new ShopService())->getProductDetail($id);
        if ($detail === null) {
            return $this->error('Product not found', 404, 404);
        }

        return $this->success($detail);
    }

    public function recordProductClick(int $id = 0): \think\Response
    {
        $id = $id > 0 ? $id : (int)$this->request->post('id/d', 0);
        $id = $id > 0 ? $id : (int)$this->request->get('id/d', 0);

        if (!(new ShopService())->recordProductClick($id)) {
            return $this->error('Product not found', 404, 404);
        }

        return $this->success(['recorded' => true]);
    }

    public function productResources(int $id = 0): \think\Response
    {
        $id = $id > 0 ? $id : (int)$this->request->get('id/d', 0);
        $service = new ShopService();
        if (!$service->productExists($id)) {
            return $this->error('Product not found', 404, 404);
        }

        return $this->success($service->getProductResources($id));
    }

    public function createOrder(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, OrderCreateValidate::class);

        try {
            $order = (new ShopService())->createOrder(
                $data,
                (int)(($this->request->user ?? [])['id'] ?? 0)
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        $detail = (new ShopService())->getOrderDetail((int)$order->id);
        return $this->success($detail ?? [], 'Order created successfully');
    }

    public function readOrder($id = 0): \think\Response
    {
        $id = (int)$id > 0 ? (int)$id : (int)$this->request->get('id/d', 0);
        $detail = (new ShopService())->getOrderDetail($id);
        if ($detail === null) {
            return $this->error('Order not found', 404, 404);
        }

        return $this->success($detail);
    }

    public function lookupOrder(): \think\Response
    {
        $orderNo = trim((string)$this->request->get('order_no', ''));
        if ($orderNo === '') {
            return $this->error('Please enter an order number');
        }

        $detail = (new ShopService())->getOrderByNo($orderNo);
        if ($detail === null) {
            return $this->error('Order not found', 404, 404);
        }

        return $this->success($detail);
    }

    /**
     * 检查当前用户对指定商品是否有24小时内的有效订单
     */
    public function checkActiveOrder(): \think\Response
    {
        $productId = (int)$this->request->get('product_id/d', 0);
        $userId = (int)(($this->request->user ?? [])['id'] ?? 0);

        if ($userId <= 0 || $productId <= 0) {
            return $this->success(['has_active' => false]);
        }

        $detail = (new ShopService())->getActiveOrderByProduct($userId, $productId);
        return $this->success([
            'has_active' => $detail !== null,
            'order' => $detail,
        ]);
    }

    /**
     * 提交许愿
     */
    public function submitWish(): \think\Response
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

        $wish = \app\model\ProductWish::create([
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

        $rows = \app\model\SystemConfig::where('group_key', 'service')->select();
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

        $websiteRows = \app\model\SystemConfig::where('group_key', 'website')->select();
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
