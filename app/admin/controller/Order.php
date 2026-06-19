<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\OrderService;
use app\admin\validate\OrderCreateValidate;
use app\model\CardOrder;
use RuntimeException;

class Order extends BaseController
{
    public function index(): \think\Response
    {
        $service = new OrderService();
        $service->expireOverdueOrders();
        $page = max(1, (int)$this->request->get('page/d', 1));
        $limit = max(1, min(100, (int)$this->request->get('limit/d', 20)));
        $status = trim((string)$this->request->get('status', ''));
        $orderNo = trim((string)$this->request->get('order_no', ''));
        $productId = (int)$this->request->get('product_id/d', 0);

        $query = CardOrder::with(['product', 'user'])->order('id', 'desc');
        if ($status !== '' && in_array($status, ['pending', 'paid', 'delivered', 'cancelled', 'refunded', 'expired'], true)) {
            $query->where('status', $status);
        }
        if ($orderNo !== '') {
            $query->whereLike('order_no', '%' . $orderNo . '%');
        }
        if ($productId > 0) {
            $query->where('product_id', $productId);
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

    public function read(int $id): \think\Response
    {
        (new OrderService())->expireOverdueOrders();
        $order = CardOrder::with(['product', 'cards', 'user'])->find($id);
        if ($order === null) {
            return $this->error('订单不存在', 404, 404);
        }

        return $this->success($order);
    }

    public function save(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, OrderCreateValidate::class);

        try {
            $order = (new OrderService())->create($data);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($order, '订单创建成功');
    }

    public function update(int $id): \think\Response
    {
        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $this->validate($data, OrderCreateValidate::class);

        try {
            $order = (new OrderService())->update($id, $data);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($order, '订单更新成功');
    }

    public function delete(int $id): \think\Response
    {
        try {
            (new OrderService())->delete($id);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success([], '订单删除成功');
    }

    public function updateStatus(int $id): \think\Response
    {
        $status = trim((string)$this->request->post('status', ''));
        if (!in_array($status, ['pending', 'paid', 'delivered', 'cancelled', 'refunded', 'expired'], true)) {
            return $this->error('状态值不合法');
        }

        try {
            $order = (new OrderService())->updateStatus($id, $status, $this->adminUser());
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($order, '订单状态更新成功');
    }

    public function deliver(int $id): \think\Response
    {
        try {
            $order = (new OrderService())->deliver($id);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($order, '订单发货成功');
    }
}
