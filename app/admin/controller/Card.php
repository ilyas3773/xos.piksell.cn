<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\ProductService;
use app\admin\validate\CardBatchCreateValidate;
use app\model\Card as CardModel;
use app\model\Product;
use think\facade\Db;

class Card extends BaseController
{
    public function index(): \think\Response
    {
        $page = max(1, (int)$this->request->get('page/d', 1));
        $limit = max(1, min(200, (int)$this->request->get('limit/d', 20)));
        $productId = (int)$this->request->get('product_id/d', 0);
        $status = trim((string)$this->request->get('status', ''));
        $keyword = trim((string)$this->request->get('keyword', ''));

        $query = CardModel::with(['product', 'order'])->order('id', 'desc');
        if ($productId > 0) {
            $query->where('product_id', $productId);
        }
        if ($status !== '' && in_array($status, ['unused', 'locked', 'sold', 'invalid'], true)) {
            $query->where('status', $status);
        }
        if ($keyword !== '') {
            $query->whereLike('card_no', '%' . $keyword . '%');
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
        $card = CardModel::with(['product', 'order'])->find($id);
        if ($card === null) {
            return $this->error('卡密不存在', 404, 404);
        }

        return $this->success($card);
    }

    public function saveBatch(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, CardBatchCreateValidate::class);

        $productId = (int)$data['product_id'];
        $product = Product::find($productId);
        if ($product === null) {
            return $this->error('商品不存在');
        }

        $cards = $data['cards'] ?? [];
        $rows = [];
        $seen = [];
        foreach ($cards as $index => $card) {
            $cardNo = trim((string)($card['card_no'] ?? ''));
            $cardSecret = trim((string)($card['card_secret'] ?? ''));
            if ($cardNo === '' || $cardSecret === '') {
                return $this->error('第' . ($index + 1) . '条卡密数据格式错误');
            }

            if (isset($seen[$cardNo])) {
                return $this->error('批量数据中存在重复卡号: ' . $cardNo);
            }

            $seen[$cardNo] = true;
            $rows[] = [
                'product_id' => $productId,
                'card_no' => $cardNo,
                'card_secret' => $cardSecret,
                'status' => 'unused',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $existCount = CardModel::whereIn('card_no', array_keys($seen))->count();
        if ($existCount > 0) {
            return $this->error('上传数据中有卡号已存在，请去重后重试');
        }

        Db::transaction(function () use ($rows, $productId) {
            (new CardModel())->saveAll($rows);
            (new ProductService())->syncStock($productId);
        });

        return $this->success([
            'count' => count($rows),
        ], '卡密批量导入成功');
    }

    public function updateStatus(int $id): \think\Response
    {
        $card = CardModel::find($id);
        if ($card === null) {
            return $this->error('卡密不存在', 404, 404);
        }

        $status = trim((string)$this->request->post('status', ''));
        if (!in_array($status, ['unused', 'locked', 'sold', 'invalid'], true)) {
            return $this->error('状态值不合法');
        }

        $card->save([
            'status' => $status,
        ]);
        (new ProductService())->syncStock((int)$card->product_id);

        return $this->success($card->refresh(), '卡密状态更新成功');
    }

    public function delete(int $id): \think\Response
    {
        $card = CardModel::find($id);
        if ($card === null) {
            return $this->error('卡密不存在', 404, 404);
        }

        if ((string)$card->status === 'sold') {
            return $this->error('已售卡密不允许删除');
        }

        $productId = (int)$card->product_id;
        $card->delete();
        (new ProductService())->syncStock($productId);

        return $this->success([], '卡密删除成功');
    }
}

