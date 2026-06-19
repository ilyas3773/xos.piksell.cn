<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\validate\AnnouncementValidate;
use app\model\Announcement as AnnouncementModel;

class Announcement extends BaseController
{
    public function index(): \think\Response
    {
        $keyword = trim((string)$this->request->get('keyword', ''));
        $status = trim((string)$this->request->get('status', ''));

        $query = AnnouncementModel::order('sort', 'asc')->order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('title|summary|content', '%' . $keyword . '%');
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
        $item = AnnouncementModel::find($id);
        if ($item === null) {
            return $this->error('公告不存在', 404, 404);
        }

        return $this->success($item);
    }

    public function save(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, AnnouncementValidate::class . '.create');

        $item = AnnouncementModel::create([
            'title' => trim((string)$data['title']),
            'summary' => trim((string)($data['summary'] ?? '')),
            'content' => trim((string)($data['content'] ?? '')),
            'sort' => (int)($data['sort'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : 1,
        ]);

        return $this->success($item, '公告创建成功');
    }

    public function update(int $id): \think\Response
    {
        $item = AnnouncementModel::find($id);
        if ($item === null) {
            return $this->error('公告不存在', 404, 404);
        }

        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $data['id'] = $id;
        $this->validate($data, AnnouncementValidate::class . '.update');

        $item->save([
            'title' => trim((string)$data['title']),
            'summary' => trim((string)($data['summary'] ?? '')),
            'content' => trim((string)($data['content'] ?? '')),
            'sort' => (int)($data['sort'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : (int)$item->status,
        ]);

        return $this->success($item->refresh(), '公告更新成功');
    }

    public function delete(int $id): \think\Response
    {
        $item = AnnouncementModel::find($id);
        if ($item === null) {
            return $this->error('公告不存在', 404, 404);
        }

        $item->delete();

        return $this->success([], '公告删除成功');
    }
}
