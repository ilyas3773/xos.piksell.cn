<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\CategoryService;
use app\admin\validate\CategoryBatchValidate;
use app\admin\validate\CategoryBatchDeleteValidate;
use app\admin\validate\CategoryValidate;
use app\model\Category as CategoryModel;
use RuntimeException;
use think\facade\Db;

class Category extends BaseController
{
    public function index(): \think\Response
    {
        $service = new CategoryService();
        $groupKey = $service->normalizeGroupKey((string)$this->request->get('group', CategoryService::GROUP_TYPE));

        $rows = CategoryModel::withCount(['products', 'kindProducts'])
            ->where('group_key', $groupKey)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        foreach ($rows as &$row) {
            $row['products_count'] = $groupKey === CategoryService::GROUP_KIND
                ? (int)($row['kind_products_count'] ?? 0)
                : (int)($row['products_count'] ?? 0);
            unset($row['kind_products_count']);
        }
        unset($row);

        $tree = $service->buildTree($rows);
        $flat = $service->buildFlat($tree);

        return $this->success([
            'tree' => $tree,
            'flat' => $flat,
            'max_level' => CategoryService::MAX_LEVEL,
            'group_key' => $groupKey,
        ]);
    }

    public function save(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, CategoryValidate::class . '.create');
        $data['group_key'] = (new CategoryService())->normalizeGroupKey((string)($data['group_key'] ?? $this->request->post('group_key', CategoryService::GROUP_TYPE)));

        try {
            $category = Db::transaction(function () use ($data) {
                return (new CategoryService())->createCategory($data);
            });
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($category, '分类创建成功');
    }

    public function saveBatch(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, CategoryBatchValidate::class);
        $data['group_key'] = (new CategoryService())->normalizeGroupKey((string)($data['group_key'] ?? $this->request->post('group_key', CategoryService::GROUP_TYPE)));

        try {
            $result = Db::transaction(function () use ($data) {
                return (new CategoryService())->createBatch($data);
            });
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($result, '分类批量创建成功');
    }

    public function update(int $id): \think\Response
    {
        $category = CategoryModel::find($id);
        if ($category === null) {
            return $this->error('分类不存在', 404, 404);
        }

        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $data['id'] = $id;
        $this->validate($data, CategoryValidate::class . '.update');

        try {
            Db::transaction(function () use ($category, $data) {
                $service = new CategoryService();
                $parentId = (int)($data['parent_id'] ?? 0);
                $groupKey = $service->normalizeGroupKey((string)$category->group_key);
                $level = $service->ensureMoveAllowed((int)$category->id, $parentId, $groupKey);

                $category->save([
                    'group_key' => $groupKey,
                    'parent_id' => $parentId,
                    'name' => trim((string)$data['name']),
                    'level' => $level,
                    'sort' => (int)($data['sort'] ?? 0),
                    'status' => isset($data['status']) ? (int)$data['status'] : (int)$category->status,
                    'description' => trim((string)($data['description'] ?? '')),
                ]);

                $service->syncLevels((int)$category->id);
            });
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($category->refresh(), '分类更新成功');
    }

    public function delete(int $id): \think\Response
    {
        try {
            (new CategoryService())->deleteCategory($id);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === '分类不存在') {
                return $this->error($exception->getMessage(), 404, 404);
            }

            return $this->error($exception->getMessage());
        }

        return $this->success([], '分类删除成功');
    }

    public function deleteBatch(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, CategoryBatchDeleteValidate::class);

        try {
            $result = Db::transaction(function () use ($data) {
                return (new CategoryService())->deleteBatch((array)($data['ids'] ?? []));
            });
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($result, '分类批量删除成功');
    }
}
