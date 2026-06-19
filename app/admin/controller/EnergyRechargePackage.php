<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\validate\EnergyRechargePackageValidate;
use app\model\EnergyRechargePackage as EnergyRechargePackageModel;

class EnergyRechargePackage extends BaseController
{
    public function index(): \think\Response
    {
        $keyword = trim((string)$this->request->get('keyword', ''));
        $status = trim((string)$this->request->get('status', ''));

        $query = EnergyRechargePackageModel::order('sort', 'asc')->order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('name|description', '%' . $keyword . '%');
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
        $item = EnergyRechargePackageModel::find($id);
        if ($item === null) {
            return $this->error('能量套餐不存在', 404, 404);
        }

        return $this->success($item);
    }

    public function save(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, EnergyRechargePackageValidate::class . '.create');

        $item = EnergyRechargePackageModel::create($this->buildPayload($data));

        return $this->success($item, '能量套餐创建成功');
    }

    public function update(int $id): \think\Response
    {
        $item = EnergyRechargePackageModel::find($id);
        if ($item === null) {
            return $this->error('能量套餐不存在', 404, 404);
        }

        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $data['id'] = $id;
        $this->validate($data, EnergyRechargePackageValidate::class . '.update');

        $item->save($this->buildPayload($data, (int)$item->status));

        return $this->success($item->refresh(), '能量套餐更新成功');
    }

    public function delete(int $id): \think\Response
    {
        $item = EnergyRechargePackageModel::find($id);
        if ($item === null) {
            return $this->error('能量套餐不存在', 404, 404);
        }

        $item->delete();
        return $this->success([], '能量套餐删除成功');
    }

    private function buildPayload(array $data, int $defaultStatus = 1): array
    {
        $energyValue = (int)$data['energy_value'];
        $bonusEnergy = (int)($data['bonus_energy'] ?? 0);

        return [
            'name' => trim((string)$data['name']),
            'energy_value' => $energyValue,
            'bonus_energy' => $bonusEnergy,
            'amount' => round((float)$data['amount'], 2),
            'sort' => (int)($data['sort'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : $defaultStatus,
            'description' => trim((string)($data['description'] ?? '')),
        ];
    }
}
