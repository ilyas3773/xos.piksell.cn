<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\EnergySourceService;
use app\admin\validate\EnergySourceValidate;
use app\model\EnergySource as EnergySourceModel;

class EnergySource extends BaseController
{
    public function index(): \think\Response
    {
        (new EnergySourceService())->syncDefaults();

        $keyword = trim((string)$this->request->get('keyword', ''));
        $status = trim((string)$this->request->get('status', ''));

        $query = EnergySourceModel::order('sort', 'asc')->order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('name|source_key|description', '%' . $keyword . '%');
        }
        if ($status !== '' && in_array($status, ['0', '1'], true)) {
            $query->where('status', (int)$status);
        }

        return $this->success([
            'list' => $query->select(),
        ]);
    }

    public function read(int $id): \think\Response
    {
        $item = EnergySourceModel::find($id);
        if ($item === null) {
            return $this->error('能量获取方式不存在', 404, 404);
        }

        return $this->success($item);
    }

    public function save(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, EnergySourceValidate::class . '.create');

        $sourceKey = trim((string)$data['source_key']);
        if (EnergySourceModel::where('source_key', $sourceKey)->find() !== null) {
            return $this->error('方式标识已存在');
        }

        $item = EnergySourceModel::create([
            'name' => trim((string)$data['name']),
            'source_key' => $sourceKey,
            'energy_value' => (int)$data['energy_value'],
            'daily_limit' => (int)($data['daily_limit'] ?? 0),
            'sort' => (int)($data['sort'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : 1,
            'description' => trim((string)($data['description'] ?? '')),
        ]);

        return $this->success($item, '能量获取方式创建成功');
    }

    public function update(int $id): \think\Response
    {
        $item = EnergySourceModel::find($id);
        if ($item === null) {
            return $this->error('能量获取方式不存在', 404, 404);
        }

        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $data['id'] = $id;
        $this->validate($data, EnergySourceValidate::class . '.update');

        $sourceKey = trim((string)$data['source_key']);
        $exists = EnergySourceModel::where('source_key', $sourceKey)
            ->where('id', '<>', $id)
            ->find();
        if ($exists !== null) {
            return $this->error('方式标识已存在');
        }

        $item->save([
            'name' => trim((string)$data['name']),
            'source_key' => $sourceKey,
            'energy_value' => (int)$data['energy_value'],
            'daily_limit' => (int)($data['daily_limit'] ?? 0),
            'sort' => (int)($data['sort'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : (int)$item->status,
            'description' => trim((string)($data['description'] ?? '')),
        ]);

        return $this->success($item->refresh(), '能量获取方式更新成功');
    }

    public function delete(int $id): \think\Response
    {
        $item = EnergySourceModel::find($id);
        if ($item === null) {
            return $this->error('能量获取方式不存在', 404, 404);
        }

        $item->delete();
        return $this->success([], '能量获取方式删除成功');
    }
}
