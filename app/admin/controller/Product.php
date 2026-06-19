<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\CategoryService;
use app\admin\validate\ProductValidate;
use app\model\Card;
use app\model\Category;
use app\model\Product as ProductModel;

class Product extends BaseController
{
    public function index(): \think\Response
    {
        $page = max(1, (int)$this->request->get('page/d', 1));
        $limit = max(1, min(500, (int)$this->request->get('limit/d', 20)));
        $keyword = trim((string)$this->request->get('keyword', ''));
        $status = (string)$this->request->get('status', '');
        $categoryId = (int)$this->request->get('category_id/d', 0);
        $featured = (string)$this->request->get('is_featured', '');

        $query = ProductModel::with(['category', 'kindCategory'])->order('id', 'desc');
        if ($keyword !== '') {
            $this->applyKeywordFilter($query, $keyword);
        }
        if ($status !== '' && in_array($status, ['0', '1'], true)) {
            $query->where('status', (int)$status);
        }
        if ($featured !== '' && in_array($featured, ['0', '1'], true)) {
            $query->where('is_featured', (int)$featured);
        }
        if ($categoryId > 0) {
            $categoryIds = array_values(array_unique(array_merge(
                [$categoryId],
                (new CategoryService())->getDescendantIds($categoryId)
            )));
            $query->whereIn('category_id', $categoryIds);
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
        $product = ProductModel::with(['category', 'kindCategory'])->find($id);
        if ($product === null) {
            return $this->error('商品不存在', 404, 404);
        }

        return $this->success($product);
    }

    public function getCardResources(): \think\Response
    {
        $moduleType = trim((string)$this->request->get('module_type', ''));
        $scope = trim((string)$this->request->get('scope', ''));
        $keyword = trim((string)$this->request->get('keyword', ''));

        // 验证参数
        if ($moduleType === '') {
            return $this->error('请选择卡密分类');
        }
        if ($scope === '') {
            return $this->error('请选择资源范围');
        }
        if (!in_array($moduleType, ['account', 'download', 'tutorial'], true)) {
            return $this->error('不支持的卡密分类');
        }
        if (!in_array($scope, ['common', 'specific'], true)) {
            return $this->error('不支持的资源范围');
        }

        $query = \app\model\CardResource::order('id', 'desc');

        // 同时根据 module_type 和 scope 筛选
        $query->where('module_type', $moduleType);
        
        if ($scope === 'common') {
            // 通用资源：is_common = 1
            $query->where('is_common', 1);
        } else {
            // 指定商品：is_common = 0
            $query->where('is_common', 0);
        }

        // 如果有关键词，搜索标题、用户名、URL、内容、备注
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('title|username|url|content|remark', '%' . $keyword . '%');
            });
        }

        $list = $query->limit(50)->select();

        return $this->success($list);
    }

    public function save(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, ProductValidate::class . '.create');

        $categoryId = (int)($data['category_id'] ?? 0);
        $kindCategoryId = (int)($data['kind_category_id'] ?? 0);

        if ($response = $this->validateCategorySelection($categoryId, CategoryService::GROUP_TYPE, '商品只能选择类型分类')) {
            return $response;
        }
        if ($response = $this->validateCategorySelection($kindCategoryId, CategoryService::GROUP_KIND, '商品只能选择类别分类')) {
            return $response;
        }

        $product = ProductModel::create([
            'category_id' => $categoryId,
            'kind_category_id' => $kindCategoryId,
            'name' => trim((string)$data['name']),
            'name_en' => trim((string)($data['name_en'] ?? '')),
            'status' => isset($data['status']) ? (int)$data['status'] : 1,
            'is_featured' => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            'description' => trim((string)($data['description'] ?? '')),
            'cover_image' => trim((string)($data['cover_image'] ?? '')),
            'gallery_images' => $this->parseGalleryImages($data['gallery_images'] ?? []),
            'game_size' => trim((string)($data['game_size'] ?? '')),
            'supported_languages' => trim((string)($data['supported_languages'] ?? '')),
            'compatibility' => trim((string)($data['compatibility'] ?? '')),
            'exchange_energy' => (int)($data['exchange_energy'] ?? 0),
        ]);

        return $this->success($product, '商品创建成功');
    }

    public function update(int $id): \think\Response
    {
        $product = ProductModel::find($id);
        if ($product === null) {
            return $this->error('商品不存在', 404, 404);
        }

        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $data['id'] = $id;
        $this->validate($data, ProductValidate::class . '.update');

        $categoryId = (int)($data['category_id'] ?? 0);
        $kindCategoryId = (int)($data['kind_category_id'] ?? 0);

        if ($response = $this->validateCategorySelection($categoryId, CategoryService::GROUP_TYPE, '商品只能选择类型分类')) {
            return $response;
        }
        if ($response = $this->validateCategorySelection($kindCategoryId, CategoryService::GROUP_KIND, '商品只能选择类别分类')) {
            return $response;
        }

        $product->save([
            'category_id' => $categoryId,
            'kind_category_id' => $kindCategoryId,
            'name' => trim((string)$data['name']),
            'name_en' => trim((string)($data['name_en'] ?? '')),
            'status' => isset($data['status']) ? (int)$data['status'] : (int)$product->status,
            'is_featured' => isset($data['is_featured']) ? (int)$data['is_featured'] : (int)$product->is_featured,
            'description' => trim((string)($data['description'] ?? '')),
            'cover_image' => trim((string)($data['cover_image'] ?? '')),
            'gallery_images' => $this->parseGalleryImages($data['gallery_images'] ?? []),
            'game_size' => trim((string)($data['game_size'] ?? '')),
            'supported_languages' => trim((string)($data['supported_languages'] ?? '')),
            'compatibility' => trim((string)($data['compatibility'] ?? '')),
            'exchange_energy' => (int)($data['exchange_energy'] ?? 0),
        ]);

        return $this->success($product->refresh(), '商品更新成功');
    }

    public function delete(int $id): \think\Response
    {
        $product = ProductModel::find($id);
        if ($product === null) {
            return $this->error('商品不存在', 404, 404);
        }

        // 删除商品前，自动解除绑定的卡密和卡密资源
        // 1. 将绑定到该商品的卡密的 product_id 设置为 0
        Card::where('product_id', $id)->update(['product_id' => 0]);
        
        // 2. 将绑定到该商品的卡密资源的 product_id 设置为 0，is_common 设置为 1（变为通用资源）
        \app\model\CardResource::where('product_id', $id)
            ->where('is_common', 0)
            ->update(['product_id' => 0, 'is_common' => 1]);

        // 3. 删除商品
        $product->delete();
        
        return $this->success([], '商品删除成功');
    }

    /**
     * 切换商品的精选状态
     */
    public function toggleFeatured(int $id): \think\Response
    {
        $product = ProductModel::find($id);
        if ($product === null) {
            return $this->error('商品不存在', 404, 404);
        }

        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }

        $isFeatured = isset($data['is_featured']) ? (int)$data['is_featured'] : (1 - (int)$product->is_featured);
        $isFeatured = $isFeatured === 1 ? 1 : 0;

        $product->save(['is_featured' => $isFeatured]);

        return $this->success($product->refresh(), $isFeatured === 1 ? '已设为精选' : '已取消精选');
    }

    /**
     * 批量更新精选状态
     */
    public function batchToggleFeatured(): \think\Response
    {
        $data = $this->request->post();
        $ids = $data['ids'] ?? [];
        $isFeatured = isset($data['is_featured']) ? (int)$data['is_featured'] : 1;
        $isFeatured = $isFeatured === 1 ? 1 : 0;

        if (!is_array($ids) || empty($ids)) {
            return $this->error('请选择要操作的商品');
        }

        $ids = array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        });

        if (empty($ids)) {
            return $this->error('请选择有效的商品');
        }

        $count = ProductModel::whereIn('id', $ids)->update(['is_featured' => $isFeatured]);

        return $this->success([
            'count' => $count,
        ], $isFeatured === 1 ? "成功将 {$count} 个商品设为精选" : "成功取消 {$count} 个商品的精选");
    }

    public function deleteBatch(): \think\Response
    {
        $data = $this->request->post();
        $ids = $data['ids'] ?? [];

        if (!is_array($ids) || empty($ids)) {
            return $this->error('请选择要删除的商品');
        }

        // 过滤并转换为整数
        $ids = array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        });

        if (empty($ids)) {
            return $this->error('请选择有效的商品');
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            $product = ProductModel::find($id);
            if ($product === null) {
                continue;
            }

            // 解除卡密绑定
            Card::where('product_id', $id)->update(['product_id' => 0]);
            
            // 解除卡密资源绑定
            \app\model\CardResource::where('product_id', $id)
                ->where('is_common', 0)
                ->update(['product_id' => 0, 'is_common' => 1]);

            // 删除商品
            $product->delete();
            $deletedCount++;
        }

        return $this->success(['count' => $deletedCount], "成功删除 {$deletedCount} 个商品");
    }

    private function parseGalleryImages(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            $url = trim((string)$item);
            if ($url === '' || in_array($url, $result, true)) {
                continue;
            }

            $result[] = $url;
            if (count($result) >= 10) {
                break;
            }
        }

        return $result;
    }

    private function validateCategorySelection(int $categoryId, string $groupKey, string $message): ?\think\Response
    {
        if ($categoryId <= 0) {
            return null;
        }

        $category = Category::find($categoryId);
        if ($category === null || (string)$category->group_key !== $groupKey) {
            return $this->error($message);
        }

        return null;
    }

    private function applyKeywordFilter($query, string $keyword): void
    {
        $matchedCategoryIds = $this->findMatchedCategoryIds($keyword);
        $isNumericKeyword = ctype_digit($keyword);

        $query->where(function ($subQuery) use ($keyword, $matchedCategoryIds, $isNumericKeyword): void {
            $subQuery->whereLike(
                'name|name_en|description|game_size|supported_languages|compatibility',
                '%' . $keyword . '%'
            );

            if ($matchedCategoryIds !== []) {
                $subQuery->whereOr(function ($orQuery) use ($matchedCategoryIds): void {
                    $orQuery->whereIn('category_id', $matchedCategoryIds)
                        ->whereOr('kind_category_id', 'in', $matchedCategoryIds);
                });
            }

            if ($isNumericKeyword) {
                $subQuery->whereOr('id', (int)$keyword);
            }
        });
    }

    private function findMatchedCategoryIds(string $keyword): array
    {
        $matchedRows = Category::whereLike('name|description', '%' . $keyword . '%')->column('id');
        if (!is_array($matchedRows) || $matchedRows === []) {
            return [];
        }

        $categoryService = new CategoryService();
        $result = [];

        foreach ($matchedRows as $matchedId) {
            $categoryId = (int)$matchedId;
            if ($categoryId <= 0) {
                continue;
            }

            $result[] = $categoryId;
            $result = array_merge($result, $categoryService->getDescendantIds($categoryId));
        }

        return array_values(array_unique(array_map('intval', $result)));
    }
}
