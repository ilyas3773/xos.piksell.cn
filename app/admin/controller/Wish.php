<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\model\ProductWish;
use app\model\User;

class Wish extends BaseController
{
    public function index(): \think\Response
    {
        $page = max(1, (int)$this->request->get('page/d', 1));
        $limit = max(1, min(200, (int)$this->request->get('limit/d', 20)));
        $keyword = trim((string)$this->request->get('keyword', ''));
        $status = (string)$this->request->get('status', '');

        $query = ProductWish::order('id', 'desc');
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('name|description|contact', '%' . $keyword . '%');
            });
        }
        if ($status !== '' && in_array($status, ['0', '1', '2', '3'], true)) {
            $query->where('status', (int)$status);
        }

        $total = (clone $query)->count();
        $list = $query->page($page, $limit)->select()->toArray();

        // 关联用户信息
        $userIds = array_filter(array_column($list, 'user_id'), fn ($id) => (int)$id > 0);
        $users = [];
        if (!empty($userIds)) {
            $users = User::whereIn('id', $userIds)->column('username,nickname', 'id');
        }

        foreach ($list as &$row) {
            $uid = (int)$row['user_id'];
            $row['user_username'] = '';
            $row['user_nickname'] = '';
            if ($uid > 0 && isset($users[$uid])) {
                $row['user_username'] = (string)$users[$uid]['username'];
                $row['user_nickname'] = (string)$users[$uid]['nickname'];
            }
        }
        unset($row);

        return $this->success([
            'list' => $list,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
            ],
            'stats' => [
                'pending' => ProductWish::where('status', 0)->count(),
                'processing' => ProductWish::where('status', 1)->count(),
                'completed' => ProductWish::where('status', 2)->count(),
                'rejected' => ProductWish::where('status', 3)->count(),
            ],
        ]);
    }

    public function updateStatus(int $id): \think\Response
    {
        $wish = ProductWish::find($id);
        if ($wish === null) {
            return $this->error('许愿不存在', 404, 404);
        }

        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }

        $status = (int)($data['status'] ?? 0);
        if (!in_array($status, [0, 1, 2, 3], true)) {
            return $this->error('状态值不合法');
        }

        $remark = trim((string)($data['admin_remark'] ?? ''));
        if (mb_strlen($remark) > 500) {
            return $this->error('备注长度不能超过500字符');
        }

        $wish->save([
            'status' => $status,
            'admin_remark' => $remark,
        ]);

        return $this->success($wish->refresh(), '更新成功');
    }

    public function delete(int $id): \think\Response
    {
        $wish = ProductWish::find($id);
        if ($wish === null) {
            return $this->error('许愿不存在', 404, 404);
        }

        $wish->delete();
        return $this->success([], '删除成功');
    }
}
