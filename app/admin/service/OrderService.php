<?php
declare(strict_types=1);

namespace app\admin\service;

use app\model\Card;
use app\model\CardOrder;
use app\model\Product;
use app\service\ProductMetricService;
use app\service\UserEnergyService;
use RuntimeException;
use think\facade\Db;

class OrderService
{
    private const EXPIRABLE_STATUSES = ['pending', 'paid', 'delivered'];

    public function expireOverdueOrders(): void
    {
        if (!$this->hasExpiresAtColumn() || !$this->hasExpiredStatus()) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        CardOrder::whereNull('expires_at')
            ->whereNotNull('created_at')
            ->update([
                'expires_at' => Db::raw('DATE_ADD(created_at, INTERVAL 24 HOUR)'),
            ]);

        $orders = CardOrder::where('status', 'in', self::EXPIRABLE_STATUSES)
            ->where('expires_at', '<=', $now)
            ->select();

        foreach ($orders as $order) {
            if ((string)$order->status === 'paid') {
                $this->releaseLockedCards($order);
            }
            $order->save([
                'status' => 'expired',
            ]);
        }
    }

    public function create(array $data): CardOrder
    {
        $product = Product::find((int)$data['product_id']);
        if ($product === null || (int)$product->status !== 1) {
            throw new RuntimeException('商品不存在或已下架');
        }

        $quantity = (int)$data['quantity'];
        $payload = [
            'order_no' => $this->buildOrderNo(),
            'user_id' => (int)($data['user_id'] ?? 0),
            'product_id' => (int)$product->id,
            'quantity' => $quantity,
            'unit_price' => (int)$product->exchange_energy,
            'total_amount' => (int)$product->exchange_energy * $quantity,
            'status' => 'pending',
            'buyer_email' => trim((string)($data['buyer_email'] ?? '')),
            'buyer_contact' => trim((string)($data['buyer_contact'] ?? '')),
            'remark' => trim((string)($data['remark'] ?? '')),
        ];

        if ($this->hasExpiresAtColumn()) {
            $payload['expires_at'] = $this->normalizeExpiresAt((string)($data['expires_at'] ?? ''), date('Y-m-d H:i:s'));
        }

        return CardOrder::create($payload);
    }

    public function update(int $orderId, array $data): CardOrder
    {
        return Db::transaction(function () use ($orderId, $data): CardOrder {
            $order = CardOrder::lock(true)->find($orderId);
            if ($order === null) {
                throw new RuntimeException('Order not found');
            }

            $product = Product::find((int)$data['product_id']);
            if ($product === null || (int)$product->status !== 1) {
                throw new RuntimeException('商品不存在或已下架');
            }

            $quantity = (int)$data['quantity'];
            $payload = [
                'product_id' => (int)$product->id,
                'quantity' => $quantity,
                'unit_price' => (int)$product->exchange_energy,
                'total_amount' => (int)$product->exchange_energy * $quantity,
                'buyer_email' => trim((string)($data['buyer_email'] ?? '')),
                'buyer_contact' => trim((string)($data['buyer_contact'] ?? '')),
                'remark' => trim((string)($data['remark'] ?? '')),
            ];

            if ($this->hasExpiresAtColumn()) {
                $payload['expires_at'] = $this->normalizeExpiresAt((string)($data['expires_at'] ?? ''), (string)$order->created_at);
            }

            $order->save($payload);

            return $order->refresh();
        });
    }

    public function delete(int $orderId): void
    {
        Db::transaction(function () use ($orderId): void {
            $order = CardOrder::lock(true)->find($orderId);
            if ($order === null) {
                throw new RuntimeException('Order not found');
            }

            $this->releaseLockedCards($order);
            Card::where('order_id', (int)$order->id)
                ->where('status', 'in', ['sold', 'used'])
                ->update([
                    'order_id' => null,
                ]);
            $order->delete();
        });
    }

    public function updateStatus(int $orderId, string $status, array $adminUser = []): CardOrder
    {
        return Db::transaction(function () use ($orderId, $status, $adminUser): CardOrder {
            $order = CardOrder::lock(true)->find($orderId);
            if ($order === null) {
                throw new RuntimeException('Order not found');
            }

            $currentStatus = (string)$order->status;
            if ($currentStatus === $status) {
                return $order->refresh();
            }

            if ($status === 'delivered') {
                throw new RuntimeException('Use the deliver action for shipment');
            }

            if ($status === 'expired' && !$this->hasExpiredStatus()) {
                throw new RuntimeException('Please run database/card_order_expire_extend.sql first');
            }

            if (in_array($currentStatus, ['cancelled', 'refunded', 'expired'], true)) {
                throw new RuntimeException('Cancelled or refunded orders cannot be changed again');
            }

            if ($currentStatus === 'delivered' && $status !== 'refunded') {
                throw new RuntimeException('Delivered orders can only be changed to refunded');
            }

            if ($currentStatus === 'paid' && $status === 'pending') {
                throw new RuntimeException('Paid orders cannot be moved back to pending');
            }

            if ($currentStatus === 'pending' && $status === 'paid') {
                $this->reserveCards($order);
            }

            if ($currentStatus === 'pending' && $status === 'refunded') {
                throw new RuntimeException('Pending orders cannot be refunded directly');
            }

            if ($currentStatus === 'paid' && in_array($status, ['cancelled', 'refunded'], true)) {
                $this->releaseLockedCards($order);
                $this->refundOrderEnergy($order, (int)($adminUser['id'] ?? 0));
            }

            if ($currentStatus === 'paid' && $status === 'expired') {
                $this->releaseLockedCards($order);
            }

            if ($currentStatus === 'delivered' && $status === 'refunded') {
                $this->refundOrderEnergy($order, (int)($adminUser['id'] ?? 0));
            }

            $payload = [
                'status' => $status,
            ];
            if ($status === 'paid' && empty($order->pay_time)) {
                $payload['pay_time'] = date('Y-m-d H:i:s');
            }

            $order->save($payload);
            (new ProductService())->syncStock((int)$order->product_id);

            return $order->refresh();
        });
    }

    public function deliver(int $orderId): CardOrder
    {
        return Db::transaction(function () use ($orderId): CardOrder {
            $order = CardOrder::lock(true)->find($orderId);
            if ($order === null) {
                throw new RuntimeException('Order not found');
            }

            if ((string)$order->status !== 'paid') {
                throw new RuntimeException('Only paid orders can be delivered');
            }

            $lockedCards = Card::where('order_id', (int)$order->id)
                ->where('status', 'locked')
                ->limit((int)$order->quantity)
                ->lock(true)
                ->select();

            $selectedCards = [];
            foreach ($lockedCards as $card) {
                $selectedCards[] = $card;
            }

            if (count($selectedCards) < (int)$order->quantity) {
                $extraCards = Card::where('product_id', (int)$order->product_id)
                    ->where('status', 'unused')
                    ->limit((int)$order->quantity - count($selectedCards))
                    ->lock(true)
                    ->select();

                foreach ($extraCards as $extraCard) {
                    $selectedCards[] = $extraCard;
                }
            }

            if (count($selectedCards) < (int)$order->quantity) {
                throw new RuntimeException('Insufficient stock for delivery');
            }

            $cardIds = [];
            $lines = [];
            foreach ($selectedCards as $card) {
                $cardIds[] = (int)$card->id;
                $lines[] = $card->card_no . '----' . $card->card_secret;
            }

            Card::whereIn('id', $cardIds)->update([
                'status' => 'sold',
                'order_id' => (int)$order->id,
                'sold_at' => date('Y-m-d H:i:s'),
            ]);

            $order->save([
                'status' => 'delivered',
                'deliver_time' => date('Y-m-d H:i:s'),
                'deliver_content' => implode(PHP_EOL, $lines),
            ]);

            (new ProductService())->syncStock((int)$order->product_id);
            (new ProductMetricService())->recordExchange((int)$order->product_id, (int)$order->quantity);

            return $order->refresh();
        });
    }

    private function reserveCards(CardOrder $order): void
    {
        $existingCount = Card::where('order_id', (int)$order->id)
            ->where('status', 'locked')
            ->lock(true)
            ->count();
        if ($existingCount >= (int)$order->quantity) {
            return;
        }

        $cards = Card::where('product_id', (int)$order->product_id)
            ->where('status', 'unused')
            ->limit((int)$order->quantity - $existingCount)
            ->lock(true)
            ->select();

        if ($cards->count() < ((int)$order->quantity - $existingCount)) {
            throw new RuntimeException('Insufficient stock to reserve cards');
        }

        $cardIds = [];
        foreach ($cards as $card) {
            $cardIds[] = (int)$card->id;
        }

        if ($cardIds !== []) {
            Card::whereIn('id', $cardIds)->update([
                'status' => 'locked',
                'order_id' => (int)$order->id,
            ]);
        }
    }

    private function releaseLockedCards(CardOrder $order): void
    {
        Card::where('order_id', (int)$order->id)
            ->where('status', 'locked')
            ->update([
                'status' => 'unused',
                'order_id' => null,
            ]);
    }

    private function refundOrderEnergy(CardOrder $order, int $operatorId): void
    {
        $userId = (int)$order->user_id;
        $totalAmount = (int)$order->total_amount;
        if ($userId <= 0 || $totalAmount <= 0) {
            return;
        }

        $energyService = new UserEnergyService();
        $user = $energyService->lockUser($userId, false);
        $energyService->changeEnergy(
            $user,
            $totalAmount,
            'refund',
            'order_refund',
            'Refund for order ' . (string)$order->order_no,
            $operatorId
        );
    }

    private function normalizeExpiresAt(string $expiresAt, string $createdAt): string
    {
        $value = trim($expiresAt);
        if ($value !== '') {
            return date('Y-m-d H:i:s', strtotime($value));
        }

        $baseTime = strtotime($createdAt);
        if ($baseTime <= 0) {
            $baseTime = time();
        }

        return date('Y-m-d H:i:s', $baseTime + 86400);
    }

    private function hasExpiresAtColumn(): bool
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }

        $rows = Db::query("SHOW COLUMNS FROM `card_orders` LIKE 'expires_at'");
        $exists = !empty($rows);

        return $exists;
    }

    private function hasExpiredStatus(): bool
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }

        $rows = Db::query("SHOW COLUMNS FROM `card_orders` LIKE 'status'");
        $columnType = strtolower((string)($rows[0]['Type'] ?? $rows[0]['type'] ?? ''));
        $exists = strpos($columnType, "'expired'") !== false;

        return $exists;
    }

    private function buildOrderNo(): string
    {
        return 'FK' . date('YmdHis') . random_int(1000, 9999);
    }
}
