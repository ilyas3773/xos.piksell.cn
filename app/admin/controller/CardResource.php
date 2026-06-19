<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\validate\CardResourceValidate;
use app\model\CardResource as CardResourceModel;
use app\model\Product;

class CardResource extends BaseController
{
    public function index(): \think\Response
    {
        $page = max(1, (int)$this->request->get('page/d', 1));
        $limit = max(1, min(500, (int)$this->request->get('limit/d', 20)));
        $moduleType = trim((string)$this->request->get('module_type', ''));
        $productId = (int)$this->request->get('product_id/d', 0);
        $status = (string)$this->request->get('status', '');
        $keyword = trim((string)$this->request->get('keyword', ''));
        $scopeType = trim((string)$this->request->get('scope_type', ''));

        if (!in_array($moduleType, ['account', 'download', 'tutorial'], true)) {
            return $this->error('资源类型不合法');
        }

        $query = CardResourceModel::with(['product'])
            ->where('module_type', $moduleType)
            ->order('is_common', 'desc')
            ->order('sort', 'asc')
            ->order('id', 'desc');

        if ($productId > 0) {
            $query->where('product_id', $productId);
        }
        if ($status !== '' && in_array($status, ['0', '1'], true)) {
            $query->where('status', (int)$status);
        }
        if ($scopeType === 'common') {
            $query->where('is_common', 1);
        } elseif ($scopeType === 'product') {
            $query->where('is_common', 0);
        }
        if ($keyword !== '') {
            $query->whereLike('title|username|url|remark', '%' . $keyword . '%');
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
        $item = CardResourceModel::with(['product'])->find($id);
        if ($item === null) {
            return $this->error('资源不存在', 404, 404);
        }

        return $this->success($item);
    }

    public function save(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, CardResourceValidate::class . '.create');

        if ($response = $this->validatePayload($data)) {
            return $response;
        }

        $item = CardResourceModel::create($this->normalizePayload($data));

        return $this->success($item, '资源创建成功');
    }

    public function update(int $id): \think\Response
    {
        $item = CardResourceModel::find($id);
        if ($item === null) {
            return $this->error('资源不存在', 404, 404);
        }

        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $data['id'] = $id;
        $this->validate($data, CardResourceValidate::class . '.update');

        if ($response = $this->validatePayload($data)) {
            return $response;
        }

        $item->save($this->normalizePayload($data));

        return $this->success($item->refresh(), '资源更新成功');
    }

    public function delete(int $id): \think\Response
    {
        $item = CardResourceModel::find($id);
        if ($item === null) {
            return $this->error('资源不存在', 404, 404);
        }

        $item->delete();
        return $this->success([], '资源删除成功');
    }

    private function validatePayload(array $data): ?\think\Response
    {
        $moduleType = (string)($data['module_type'] ?? '');
        $isCommon = (int)($data['is_common'] ?? 0) === 1;
        $productId = $isCommon ? 0 : (int)($data['product_id'] ?? 0);
        $title = trim((string)($data['title'] ?? ''));
        $username = trim((string)($data['username'] ?? ''));
        $password = trim((string)($data['password'] ?? ''));
        $url = trim((string)($data['url'] ?? ''));
        $tutorialMode = trim((string)($data['tutorial_mode'] ?? 'url'));
        $content = trim((string)($data['content'] ?? ''));

        if (!$isCommon && $productId <= 0) {
            return $this->error('请选择对应商品，或者切换为通用资源');
        }

        if ($productId > 0 && Product::find($productId) === null) {
            return $this->error('关联商品不存在');
        }

        if ($moduleType === 'account') {
            if ($title === '') {
                return $this->error('请填写账号标题');
            }
            if ($username === '' || $password === '') {
                return $this->error('账号类资源必须填写账号和密码');
            }
        }

        if ($moduleType === 'download') {
            if ($title === '') {
                return $this->error('请填写下载项标题');
            }
            if ($url === '') {
                return $this->error('下载连接类必须填写下载网址');
            }
        }

        if ($moduleType === 'tutorial') {
            if ($title === '') {
                return $this->error('请填写教程标题');
            }
            if ($tutorialMode === 'url' && $url === '') {
                return $this->error('教程链接模式必须填写网址');
            }
            if ($tutorialMode === 'richtext' && $content === '') {
                return $this->error('教程富文本模式必须填写内容');
            }
        }

        return null;
    }

    private function normalizePayload(array $data): array
    {
        $isCommon = (int)($data['is_common'] ?? 0) === 1 ? 1 : 0;
        $moduleType = trim((string)($data['module_type'] ?? ''));
        $tutorialMode = trim((string)($data['tutorial_mode'] ?? 'url')) ?: 'url';
        $username = trim((string)($data['username'] ?? ''));
        $password = trim((string)($data['password'] ?? ''));
        $url = trim((string)($data['url'] ?? ''));
        $content = trim((string)($data['content'] ?? ''));

        if ($moduleType !== 'account') {
            $username = '';
            $password = '';
        }

        if ($moduleType === 'tutorial') {
            if ($tutorialMode === 'richtext') {
                $url = '';
            } else {
                $content = '';
            }
        } else {
            $tutorialMode = 'url';
            $content = '';
        }

        return [
            'module_type' => $moduleType,
            'product_id' => $isCommon ? 0 : (int)($data['product_id'] ?? 0),
            'is_common' => $isCommon,
            'title' => trim((string)($data['title'] ?? '')),
            'username' => $username,
            'password' => $password,
            'url' => $url,
            'tutorial_mode' => $tutorialMode,
            'content' => $content,
            'sort' => (int)($data['sort'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : 1,
            'remark' => trim((string)($data['remark'] ?? '')),
        ];
    }
}
