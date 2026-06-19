<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\UserService;
use app\admin\validate\EnergyAdjustValidate;
use app\admin\validate\UserValidate;
use app\model\EnergyLog;
use app\model\User as UserModel;
use RuntimeException;

class User extends BaseController
{
    public function index(): \think\Response
    {
        $page = max(1, (int)$this->request->get('page/d', 1));
        $limit = max(1, min(500, (int)$this->request->get('limit/d', 20)));
        $keyword = trim((string)$this->request->get('keyword', ''));
        $status = trim((string)$this->request->get('status', ''));

        $query = UserModel::order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('username|nickname|phone|email|wx_openid', '%' . $keyword . '%');
        }
        if ($status !== '' && in_array($status, ['0', '1'], true)) {
            $query->where('status', (int)$status);
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
            'summary' => [
                'user_total' => UserModel::count(),
                'enabled_total' => UserModel::where('status', 1)->count(),
                'disabled_total' => UserModel::where('status', 0)->count(),
                'energy_total' => (int)UserModel::sum('energy'),
            ],
        ]);
    }

    public function read(int $id): \think\Response
    {
        $user = UserModel::find($id);
        if ($user === null) {
            return $this->error('用户不存在', 404, 404);
        }

        return $this->success($user);
    }

    public function save(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, UserValidate::class . '.create');

        $username = trim((string)($data['username'] ?? ''));
        if (UserModel::where('username', $username)->find() !== null) {
            return $this->error('用户名已存在');
        }

        $user = UserModel::create([
            'username' => $username,
            'nickname' => trim((string)($data['nickname'] ?? '')),
            'phone' => trim((string)($data['phone'] ?? '')),
            'email' => trim((string)($data['email'] ?? '')),
            'avatar' => trim((string)($data['avatar'] ?? '')),
            'wx_openid' => trim((string)($data['wx_openid'] ?? '')),
            'energy' => (int)($data['energy'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : 1,
            'remark' => trim((string)($data['remark'] ?? '')),
        ]);

        return $this->success($user, '用户创建成功');
    }

    public function update(int $id): \think\Response
    {
        $user = UserModel::find($id);
        if ($user === null) {
            return $this->error('用户不存在', 404, 404);
        }

        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $data['id'] = $id;
        $this->validate($data, UserValidate::class . '.update');

        $username = trim((string)($data['username'] ?? ''));
        $exists = UserModel::where('username', $username)
            ->where('id', '<>', $id)
            ->find();
        if ($exists !== null) {
            return $this->error('用户名已存在');
        }

        $user->save([
            'username' => $username,
            'nickname' => trim((string)($data['nickname'] ?? '')),
            'phone' => trim((string)($data['phone'] ?? '')),
            'email' => trim((string)($data['email'] ?? '')),
            'avatar' => trim((string)($data['avatar'] ?? '')),
            'wx_openid' => trim((string)($data['wx_openid'] ?? '')),
            'energy' => (int)($data['energy'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : (int)$user->status,
            'remark' => trim((string)($data['remark'] ?? '')),
        ]);

        return $this->success($user->refresh(), '用户更新成功');
    }

    public function delete(int $id): \think\Response
    {
        $user = UserModel::find($id);
        if ($user === null) {
            return $this->error('用户不存在', 404, 404);
        }

        EnergyLog::where('user_id', $id)->delete();
        $user->delete();
        return $this->success([], '用户删除成功');
    }

    public function adjustEnergy(int $id): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, EnergyAdjustValidate::class);

        try {
            $user = (new UserService())->adjustEnergy(
                $id,
                (int)$data['change_amount'],
                trim((string)($data['remark'] ?? '')),
                $this->adminUser()
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($user, '能量调整成功');
    }
}
