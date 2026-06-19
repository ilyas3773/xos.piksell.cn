<?php
declare(strict_types=1);

namespace app\admin\service;

use app\model\Category;
use app\model\Product;
use RuntimeException;

class CategoryService
{
    public const MAX_LEVEL = 4;
    public const GROUP_TYPE = 'type';
    public const GROUP_KIND = 'kind';
    private const BATCH_LIMIT = 500;

    public function createCategory(array $data): Category
    {
        $groupKey = $this->normalizeGroupKey((string)($data['group_key'] ?? self::GROUP_TYPE));
        $parentId = (int)($data['parent_id'] ?? 0);
        $level = $this->resolveLevel($parentId, 0, $groupKey);

        return Category::create([
            'group_key' => $groupKey,
            'parent_id' => $parentId,
            'name' => trim((string)$data['name']),
            'level' => $level,
            'sort' => (int)($data['sort'] ?? 0),
            'status' => isset($data['status']) ? (int)$data['status'] : 1,
            'description' => trim((string)($data['description'] ?? '')),
        ]);
    }

    public function createBatch(array $data): array
    {
        $groupKey = $this->normalizeGroupKey((string)($data['group_key'] ?? self::GROUP_TYPE));
        $parentId = (int)($data['parent_id'] ?? 0);
        $level = $this->resolveLevel($parentId, 0, $groupKey);
        $names = $this->normalizeBatchNames((array)($data['names'] ?? []));
        $sortStart = (int)($data['sort_start'] ?? 0);
        $sortStep = (int)($data['sort_step'] ?? 10);
        $status = isset($data['status']) ? (int)$data['status'] : 1;
        $description = trim((string)($data['description'] ?? ''));

        $rows = [];
        foreach ($names as $index => $name) {
            $rows[] = [
                'group_key' => $groupKey,
                'parent_id' => $parentId,
                'name' => $name,
                'level' => $level,
                'sort' => $sortStart + ($index * $sortStep),
                'status' => $status,
                'description' => $description,
            ];
        }

        $list = (new Category())->saveAll($rows)->toArray();

        return [
            'count' => count($list),
            'list' => $list,
        ];
    }

    public function deleteCategory(int $id, array $selectedIds = []): void
    {
        $category = Category::find($id);
        if ($category === null) {
            throw new RuntimeException('分类不存在');
        }

        $childQuery = Category::where('parent_id', $id);
        if ($selectedIds !== []) {
            $childQuery->whereNotIn('id', $selectedIds);
        }

        if ($childQuery->count() > 0) {
            throw new RuntimeException('请先删除子分类');
        }

        if (Product::where('category_id', $id)->count() > 0 || Product::where('kind_category_id', $id)->count() > 0) {
            throw new RuntimeException('该分类下存在商品，无法删除');
        }

        $category->delete();
    }

    public function deleteBatch(array $ids): array
    {
        $ids = $this->normalizeDeleteIds($ids);
        $foundIds = Category::whereIn('id', $ids)->column('id');

        if (count($foundIds) !== count($ids)) {
            throw new RuntimeException('部分分类不存在或已被删除');
        }

        $list = Category::whereIn('id', $ids)
            ->order('level', 'desc')
            ->order('id', 'desc')
            ->select();

        foreach ($list as $category) {
            $this->deleteCategory((int)$category->id, $ids);
        }

        return [
            'count' => count($ids),
        ];
    }

    public function buildTree(array $rows, int $parentId = 0): array
    {
        $tree = [];

        foreach ($rows as $row) {
            if ((int)$row['parent_id'] !== $parentId) {
                continue;
            }

            $row['children'] = $this->buildTree($rows, (int)$row['id']);
            $tree[] = $row;
        }

        usort($tree, static function (array $left, array $right): int {
            if ((int)$left['sort'] === (int)$right['sort']) {
                return (int)$left['id'] <=> (int)$right['id'];
            }

            return (int)$left['sort'] <=> (int)$right['sort'];
        });

        return $tree;
    }

    public function buildFlat(array $tree, string $prefix = ''): array
    {
        $flat = [];

        foreach ($tree as $node) {
            $item = $node;
            unset($item['children']);
            $item['title'] = $prefix . $item['name'];
            $flat[] = $item;

            if (!empty($node['children'])) {
                $flat = array_merge($flat, $this->buildFlat($node['children'], $prefix . '    '));
            }
        }

        return $flat;
    }

    public function resolveLevel(int $parentId, int $selfId = 0, ?string $groupKey = null): int
    {
        if ($parentId === 0) {
            return 1;
        }

        $parent = Category::find($parentId);
        if ($parent === null) {
            throw new RuntimeException('父级分类不存在');
        }

        if ($groupKey !== null && (string)$parent->group_key !== $this->normalizeGroupKey($groupKey)) {
            throw new RuntimeException('父级分类分组不匹配');
        }

        if ($selfId > 0 && $parentId === $selfId) {
            throw new RuntimeException('分类不能挂到自己下面');
        }

        $level = (int)$parent->level + 1;
        if ($level > self::MAX_LEVEL) {
            throw new RuntimeException('最多只支持4级分类');
        }

        return $level;
    }

    public function ensureMoveAllowed(int $categoryId, int $newParentId, ?string $groupKey = null): int
    {
        $category = Category::find($categoryId);
        if ($category === null) {
            throw new RuntimeException('分类不存在');
        }

        $targetGroupKey = $this->normalizeGroupKey((string)($groupKey ?? $category->group_key ?? self::GROUP_TYPE));

        if ((string)$category->group_key !== $targetGroupKey) {
            throw new RuntimeException('分类分组不匹配');
        }

        $descendantIds = $this->getDescendantIds($categoryId);
        if (in_array($newParentId, $descendantIds, true)) {
            throw new RuntimeException('父级分类不能选择当前分类的子级');
        }

        $newLevel = $this->resolveLevel($newParentId, $categoryId, $targetGroupKey);
        $subtreeHeight = $this->getSubtreeHeight($categoryId);

        if ($newLevel + $subtreeHeight - 1 > self::MAX_LEVEL) {
            throw new RuntimeException('移动后会超过4级分类限制');
        }

        return $newLevel;
    }

    public function syncLevels(int $categoryId): void
    {
        $category = Category::find($categoryId);
        if ($category === null) {
            return;
        }

        $children = Category::where('parent_id', $categoryId)->select();
        foreach ($children as $child) {
            $childLevel = (int)$category->level + 1;
            if ($childLevel > self::MAX_LEVEL) {
                throw new RuntimeException('分类层级超过限制');
            }

            $child->save([
                'level' => $childLevel,
            ]);

            $this->syncLevels((int)$child->id);
        }
    }

    public function getDescendantIds(int $categoryId): array
    {
        $rows = Category::field(['id', 'parent_id'])->select()->toArray();
        $result = [];
        $queue = [$categoryId];

        while (!empty($queue)) {
            $current = array_shift($queue);
            foreach ($rows as $row) {
                if ((int)$row['parent_id'] !== $current) {
                    continue;
                }

                $childId = (int)$row['id'];
                $result[] = $childId;
                $queue[] = $childId;
            }
        }

        return $result;
    }

    private function getSubtreeHeight(int $categoryId): int
    {
        $rows = Category::field(['id', 'parent_id'])->select()->toArray();
        return $this->getHeightFromRows($rows, $categoryId);
    }

    private function normalizeBatchNames(array $names): array
    {
        $result = [];
        $seen = [];

        foreach ($names as $rawName) {
            $name = trim((string)$rawName);
            if ($name === '') {
                continue;
            }

            if (mb_strlen($name, 'UTF-8') > 100) {
                throw new RuntimeException('分类名称不能超过100个字符');
            }

            if (isset($seen[$name])) {
                continue;
            }

            $seen[$name] = true;
            $result[] = $name;

            if (count($result) > self::BATCH_LIMIT) {
                throw new RuntimeException('单次最多只能批量添加500个分类');
            }
        }

        if ($result === []) {
            throw new RuntimeException('请至少填写一个有效的分类名称');
        }

        return $result;
    }

    public function normalizeGroupKey(string $groupKey): string
    {
        $normalized = trim(strtolower($groupKey));

        if ($normalized === self::GROUP_KIND) {
            return self::GROUP_KIND;
        }

        return self::GROUP_TYPE;
    }

    private function normalizeDeleteIds(array $ids): array
    {
        $result = [];
        $seen = [];

        foreach ($ids as $rawId) {
            $id = (int)$rawId;
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $result[] = $id;

            if (count($result) > self::BATCH_LIMIT) {
                throw new RuntimeException('单次最多只能删除500个分类');
            }
        }

        if ($result === []) {
            throw new RuntimeException('请至少选择一个有效分类');
        }

        return $result;
    }

    private function getHeightFromRows(array $rows, int $categoryId): int
    {
        $max = 1;

        foreach ($rows as $row) {
            if ((int)$row['parent_id'] !== $categoryId) {
                continue;
            }

            $height = 1 + $this->getHeightFromRows($rows, (int)$row['id']);
            if ($height > $max) {
                $max = $height;
            }
        }

        return $max;
    }
}
