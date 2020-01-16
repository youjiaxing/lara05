<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2019/12/31 18:33
 */

namespace App\Services;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseTimeoutOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class OrderService
{
    /**
     * 新增订单
     *
     * @param User        $user
     * @param UserAddress $address
     * @param array       $items
     * @param string      $remark
     *
     * @return Order
     * @throws \Exception|\Throwable
     *
     */
    public function store(User $user, UserAddress $address, array $items, string $remark)
    {
        // 根据 sku_id 排序, 减少发生死锁概率
        $items = collect($items)->sortBy('product_sku_id', SORT_NUMERIC);

        $order = DB::transaction(function () use ($user, $address, $items, $remark) {
            $productSkus = ProductSku::query()->whereIn('id', $items->pluck('product_sku_id'))->get()->keyBy('id');
            if ($productSkus->count() != count($items)) {
                throw new InvalidRequestException("存在无效的商品");
            }

            // 更新地址最后使用时间
            $address->touch();
//            $address->update(['last_used_at' => now()]);

            // 创建订单子记录
            $orderItems = collect();
            $requireSkuQuantities = $items->pluck('amount', 'product_sku_id');
            foreach ($productSkus as $productSku) {
                $requireQuantities = $requireSkuQuantities[$productSku->id];
                /* @var ProductSku $productSku */
                if ($productSku->stock <= 0 || $productSku->stock < $requireQuantities) {
                    throw new InvalidRequestException("商品库存不足");
                }

                $orderItem = new OrderItem();
                $orderItem->forceFill([
                    'user_id' => $user->id,
                    'product_sku_id' => $productSku->id,
                    'product_id' => $productSku->product_id,

                    'quantity' => $requireQuantities,
                    'amount' => $requireQuantities * $productSku->price,

                    'refund_status' => OrderItem::REFUND_STATUS_PENDING,
                    'refund_quantity' => 0,
                    'refund_amount' => 0,
                ]);

                $orderItem['paid_amount'] = $orderItem['amount'];

                $orderItems->push($orderItem);
            }

            // 创建订单主记录
            $order = new Order();
            $order->forceFill([
                'no' => static::genOrderNo(),
                'user_id' => $user->id,

                'status' => Order::ORDER_STATUS_CREATED,
                'amount' => $orderItems->sum('amount'),
                'address' => $address->only(['contact_name', 'contact_phone', 'zip', 'province', 'city', 'district', 'address']),
                'remark' => $remark,
            ]);
            $order['paid_amount'] = $order->amount;

            $order->save();
            $order->orderItems()->saveMany($orderItems);

            // 删除购物车相关商品
            /* @var CartService $cartService */
            $cartService = app(CartService::class);
            $cartService->removeProductSkus($user, $productSkus);

            // 扣除库存
            foreach ($productSkus as $productSku) {
                $requireQuantities = $requireSkuQuantities[$productSku->id];
                if (!$productSku->decreaseStock($requireQuantities)) {
                    // 库存不足
                    throw new InvalidRequestException("商品库存不足");
                }
            }

            return $order;
        });

        // 超时未付款则自动取消订单
        CloseTimeoutOrder::dispatch($order)->delay(now()->addSeconds(config('shop.order_ttl')));

        return $order;
    }

    const ORDER_NO_XOR_MAX = 67108863;  // 26位比特全1

    /**
     * 生成商品订单流水号: 前10位为日期+时间, 后8位是自增序号
     *
     * @param User $user
     *
     * @return int
     */
    public static function genOrderNo()
    {
        // ymdHi 占用10位
        // 自增序号占用8位: 99999999, 对该序号进行异或操作, 为确保能还原数据, 自增序号不应大于 67108863(26位比特), 否则异或之后会超出
        $dateTime = \Carbon\Carbon::now()->format("ymdHi");
        $dateTimeXor = crc32($dateTime) % static::ORDER_NO_XOR_MAX;
        $orderNoInc = Redis::connection()->incr('inc:order_no');
        $orderNoXor = ($orderNoInc % static::ORDER_NO_XOR_MAX) ^ (static::ORDER_NO_XOR_MAX - (int)$dateTimeXor % static::ORDER_NO_XOR_MAX) ^ config('shop.order_xor');
        $orderNo = $dateTime . str_pad($orderNoXor, 8, '0', STR_PAD_LEFT);
        return (int)$orderNo;
    }

    /**
     * 还原订单流水号(还原后8位)
     *
     * @param $orderNo
     *
     * @return int
     */
    public static function restoreOrderNo($orderNo)
    {
        $dateTime = substr($orderNo, 0, 10);
        $dateTimeXor = crc32($dateTime) % static::ORDER_NO_XOR_MAX;
        $orderNoXor = substr($orderNo, 10);
        $orderNoInc = $orderNoXor ^ config('shop.order_xor') ^ (static::ORDER_NO_XOR_MAX - (int)$dateTimeXor % static::ORDER_NO_XOR_MAX);
        return intval($dateTime . str_pad($orderNoInc, 8, '0', STR_PAD_LEFT));
    }

    public function paid(Order $order, $paymentMethod, $paymentNo, $paidAt)
    {
        // 业务逻辑处理
        if ($order->isPaid()) {
            // TODO 可能需要退款
            throw new \Exception("订单重复支付");
        }

        if ($order->status != Order::ORDER_STATUS_CREATED) {
            //TODO 可能需要退款
            throw new \Exception("订单状态错误");
        }

        // 更新订单状态
        $order->forceFill([
            'paid_at' => $paidAt,
            'payment_method' => $paymentMethod,
            'payment_no' => $paymentNo,
            'status' => Order::ORDER_STATUS_PAID,
        ]);
        $order->save();

        OrderPaid::dispatch($order);
    }
}
