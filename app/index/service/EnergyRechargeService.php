<?php
declare(strict_types=1);

namespace app\index\service;

use app\model\EnergyRechargeOrder;
use app\model\SystemConfig;
use RuntimeException;
use think\facade\Db;

class EnergyRechargeService
{
    private const PAY_CHANNELS = ['wechat', 'alipay', 'epay'];

    public function getPackages(): array
    {
        $this->assertRechargeTablesReady();

        $rows = Db::name('energy_recharge_packages')
            ->where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return array_map(fn (array $row): array => $this->formatPackage($row), $rows);
    }

    public function createOrder(int $userId, int $packageId, string $payChannel, string $returnUrl = ''): array
    {
        $this->assertRechargeTablesReady();

        if ($userId <= 0) {
            throw new RuntimeException('请先登录');
        }

        $payChannel = strtolower(trim($payChannel));
        if (!in_array($payChannel, self::PAY_CHANNELS, true)) {
            throw new RuntimeException('支付方式不支持');
        }

        $package = Db::name('energy_recharge_packages')
            ->where('id', $packageId)
            ->where('status', 1)
            ->find();
        if (!$package) {
            throw new RuntimeException('充值套餐不存在或已下架');
        }

        $channelStatus = $this->getPayChannelStatus($payChannel);
        if (!$channelStatus['enabled']) {
            $missing = $channelStatus['missing'] ?? [];
            throw new RuntimeException(!empty($missing) ? implode('；', $missing) : '支付配置未完成，请先在后台系统配置中完善');
        }

        $order = EnergyRechargeOrder::create([
            'order_no' => $this->buildRechargeOrderNo(),
            'user_id' => $userId,
            'package_id' => (int)$package['id'],
            'package_name' => (string)$package['name'],
            'energy_value' => (int)$package['energy_value'],
            'bonus_energy' => (int)$package['bonus_energy'],
            'total_energy' => (int)$package['energy_value'] + (int)$package['bonus_energy'],
            'amount' => (float)$package['amount'],
            'pay_channel' => $payChannel,
            'status' => 'pending',
            'remark' => 'Waiting for payment gateway integration',
        ]);

        if ($payChannel === 'wechat') {
            $payment = (new WeChatPayService())->createJsapiPayment($order, $userId);

            return [
                'order' => $this->formatOrder($order->refresh()->toArray()),
                'pay_channel' => $payChannel,
                'pay_status' => 'ready',
                'payment' => $payment['pay_params'],
                'message' => '微信预支付订单已创建',
            ];
        }

        if ($payChannel === 'epay') {
            $payment = (new EpayService())->createPayment($order, $returnUrl);

            return [
                'order' => $this->formatOrder($order->refresh()->toArray()),
                'pay_channel' => $payChannel,
                'pay_status' => 'ready',
                'payment' => $payment,
                'message' => '易支付订单已创建',
            ];
        }

        return [
            'order' => $this->formatOrder($order->toArray()),
            'pay_channel' => $payChannel,
            'pay_status' => 'gateway_pending',
            'message' => '充值订单已创建，微信/支付宝统一下单接口待接入',
        ];
    }

    public function getPayChannelStatus(string $payChannel): array
    {
        if ($payChannel === 'wechat') {
            try {
                (new WeChatPayService())->getConfig();

                return [
                    'enabled' => true,
                    'missing' => [],
                    'message' => '微信支付已配置',
                ];
            } catch (RuntimeException $exception) {
                return [
                    'enabled' => false,
                    'missing' => [$exception->getMessage()],
                    'message' => $exception->getMessage(),
                ];
            }
        }

        if ($payChannel === 'epay') {
            try {
                (new EpayService())->getConfig();

                return [
                    'enabled' => true,
                    'missing' => [],
                    'message' => '易支付已配置',
                ];
            } catch (RuntimeException $exception) {
                return [
                    'enabled' => false,
                    'missing' => [$exception->getMessage()],
                    'message' => $exception->getMessage(),
                ];
            }
        }

        $groupKey = $payChannel === 'wechat' ? 'wechat_pay' : 'alipay';
        $requiredKeys = $payChannel === 'wechat'
            ? ['merchant_id', 'api_v3_key', 'cert_serial_no', 'notify_url']
            : ['app_id', 'merchant_private_key', 'alipay_public_key', 'notify_url'];

        $rows = SystemConfig::where('group_key', $groupKey)->select();
        $values = [];
        foreach ($rows as $row) {
            $values[(string)$row->config_key] = trim((string)$row->config_value);
        }

        $missing = [];
        foreach ($requiredKeys as $key) {
            if (($values[$key] ?? '') === '') {
                $missing[] = $key;
            }
        }

        return [
            'enabled' => count($missing) === 0,
            'missing' => $missing,
            'message' => count($missing) === 0 ? '支付已配置' : ('缺少配置：' . implode('、', $missing)),
        ];
    }

    public function getOrderStatus(int $userId, string $orderNo): array
    {
        $this->assertRechargeTablesReady();

        if ($userId <= 0) {
            throw new RuntimeException('请先登录');
        }

        $orderNo = trim($orderNo);
        if ($orderNo === '') {
            throw new RuntimeException('充值订单号不能为空');
        }

        $order = EnergyRechargeOrder::where('order_no', $orderNo)
            ->where('user_id', $userId)
            ->find();
        if ($order === null) {
            throw new RuntimeException('充值订单不存在');
        }

        $orderData = $this->formatOrder($order->toArray());
        if ($orderData['pay_channel'] === 'epay' && $orderData['status'] === 'pending') {
            try {
                (new EpayService())->syncEnergyRechargeOrder($orderNo);
                $order = EnergyRechargeOrder::where('order_no', $orderNo)
                    ->where('user_id', $userId)
                    ->find();
                if ($order !== null) {
                    $orderData = $this->formatOrder($order->toArray());
                }
            } catch (RuntimeException) {
            }
        }

        return [
            'order' => $orderData,
            'paid' => $orderData['status'] === 'paid',
            'status' => $orderData['status'],
        ];
    }

    private function assertRechargeTablesReady(): void
    {
        if (!$this->tableExists('energy_recharge_packages') || !$this->tableExists('energy_recharge_orders')) {
            throw new RuntimeException('请先执行 database/energy_recharge_extend.sql 初始化能量充值表');
        }
    }

    private function tableExists(string $table): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return false;
        }

        $rows = Db::query("SHOW TABLES LIKE '{$table}'");

        return !empty($rows);
    }

    private function formatPackage(array $row): array
    {
        $energyValue = (int)($row['energy_value'] ?? 0);
        $bonusEnergy = (int)($row['bonus_energy'] ?? 0);

        return [
            'id' => (int)($row['id'] ?? 0),
            'name' => (string)($row['name'] ?? ''),
            'energy_value' => $energyValue,
            'bonus_energy' => $bonusEnergy,
            'total_energy' => $energyValue + $bonusEnergy,
            'amount' => (float)($row['amount'] ?? 0),
            'sort' => (int)($row['sort'] ?? 0),
            'status' => (int)($row['status'] ?? 0),
            'description' => (string)($row['description'] ?? ''),
        ];
    }

    private function formatOrder(array $row): array
    {
        return [
            'id' => (int)($row['id'] ?? 0),
            'order_no' => (string)($row['order_no'] ?? ''),
            'package_id' => (int)($row['package_id'] ?? 0),
            'package_name' => (string)($row['package_name'] ?? ''),
            'energy_value' => (int)($row['energy_value'] ?? 0),
            'bonus_energy' => (int)($row['bonus_energy'] ?? 0),
            'total_energy' => (int)($row['total_energy'] ?? 0),
            'amount' => (float)($row['amount'] ?? 0),
            'pay_channel' => (string)($row['pay_channel'] ?? ''),
            'status' => (string)($row['status'] ?? ''),
            'created_at' => (string)($row['created_at'] ?? ''),
        ];
    }

    private function buildRechargeOrderNo(): string
    {
        return 'CZ' . date('YmdHis') . random_int(1000, 9999);
    }
}
