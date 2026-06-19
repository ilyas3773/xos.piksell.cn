<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\model\Card;
use app\model\CardOrder;
use app\model\Category;
use app\model\Product;

class Dashboard extends BaseController
{
    public function health(): \think\Response
    {
        $checks = [];
        $allOk = true;

        // 检查数据库连接
        try {
            \think\facade\Db::query('SELECT 1');
            $checks['database'] = ['status' => 'ok', 'message' => '连接正常'];
        } catch (\Throwable $e) {
            $allOk = false;
            $checks['database'] = [
                'status' => 'error',
                'message' => '数据库连接失败: ' . $e->getMessage(),
            ];
        }

        // 检查安装状态
        $installed = (new \app\service\InstallerService())->isInstalled();
        $checks['installation'] = [
            'status' => $installed ? 'ok' : 'error',
            'message' => $installed ? '已安装' : '未完成安装',
        ];

        if (!$installed) {
            $allOk = false;
        }

        return $this->success([
            'status' => $allOk ? 'ok' : 'error',
            'checks' => $checks,
            'time' => date('Y-m-d H:i:s'),
        ], $allOk ? 'ok' : '系统检查未通过');
    }

    public function stats(): \think\Response
    {
        $summary = [
            'category_total' => Category::count(),
            'product_total' => Product::count(),
            'card_total' => Card::count(),
            'card_unused' => Card::where('status', 'unused')->count(),
            'card_sold' => Card::where('status', 'sold')->count(),
            'order_pending' => CardOrder::where('status', 'pending')->count(),
            'order_paid' => CardOrder::where('status', 'paid')->count(),
            'order_delivered' => CardOrder::where('status', 'delivered')->count(),
            'today_revenue' => (float)CardOrder::where('status', 'in', ['paid', 'delivered'])
                ->whereTime('pay_time', 'today')
                ->sum('total_amount'),
            'total_revenue' => (float)CardOrder::where('status', 'in', ['paid', 'delivered'])
                ->sum('total_amount'),
        ];

        return $this->success($summary, 'ok');
    }
}
