<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\validate\FaqValidate;
use app\model\Faq as FaqModel;

class Faq extends BaseController
{
    public function index(): \think\Response
    {
        $keyword = trim((string)$this->request->get('keyword', ''));
        $status = trim((string)$this->request->get('status', ''));

        $query = FaqModel::order('sort', 'asc')->order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('question|answer', '%' . $keyword . '%');
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
        $item = FaqModel::find($id);
        if ($item === null) {
            return $this->error('常见问题不存在', 404, 404);
        }

        return $this->success($item);
    }

    public function save(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, FaqValidate::class . '.create');

        $item = FaqModel::create([
            'question' => trim((string)$data['question']),
            'answer' => trim((string)($data['answer'] ?? '')),
            'image' => trim((string)($data['image'] ?? '')),
            'sort' => (int)($data['sort'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : 1,
        ]);

        return $this->success($item, '常见问题创建成功');
    }

    public function update(int $id): \think\Response
    {
        $item = FaqModel::find($id);
        if ($item === null) {
            return $this->error('常见问题不存在', 404, 404);
        }

        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $data['id'] = $id;
        $this->validate($data, FaqValidate::class . '.update');

        $item->save([
            'question' => trim((string)$data['question']),
            'answer' => trim((string)($data['answer'] ?? '')),
            'image' => trim((string)($data['image'] ?? '')),
            'sort' => (int)($data['sort'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : (int)$item->status,
        ]);

        return $this->success($item->refresh(), '常见问题更新成功');
    }

    public function delete(int $id): \think\Response
    {
        $item = FaqModel::find($id);
        if ($item === null) {
            return $this->error('常见问题不存在', 404, 404);
        }

        $item->delete();
        return $this->success([], '常见问题删除成功');
    }
}
