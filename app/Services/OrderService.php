<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2019/12/31 18:33
 */

namespace App\Services;

use App\Events\OrderPaid;
use App\Exceptions\InternalException;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseTimeoutOrder;
use App\Models\Coupon;
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
     * @param User             $user
     * @param UserAddress      $address
     * @param array|Collection $items = [
     *                                ['quantity' => 1, 'product_sku_id' => 123],
     *                                ]
     * @param string           $remark
     * @param Coupon|null      $coupon
     *
     * @return Order
     * @throws \App\Exceptions\InvalidCouponException
     */
    public function store(User $user, UserAddress $address, $items, string $remark, Coupon $coupon = null)
    {
        $couponService = app(CouponService::class);
        if ($coupon) {
            $couponService->checkValid($coupon, null, $user);
        }

        $items = collect($items)->keyBy('product_sku_id')->toArray();

        $order = DB::transaction(function () use ($coupon, $user, $address, &$items, $remark, $couponService) {
            // 查找本次要购买的商品SKU, 并校验是否都是有效的
            $productSkus = ProductSku::query()->whereIn('id', array_keys($items))->orderBy('id')->with('product')->get()->keyBy('id');
            if ($productSkus->count() != count($items)) {
                throw new InvalidRequestException("存在无效的商品");
            }

            // 计算总金额, 初步确认商品状态及库存
            foreach ($productSkus as $id => $productSku) {
                /* @var ProductSku $productSku */
                if (!$productSku->product->is_sale) {
                    throw new InvalidRequestException("部分商品已下架");
                }

                if ($productSku->stock < $items[$id]['quantity']) {
                    throw new InvalidRequestException("商品库存不足");
                }

                $items[$id]['total_amount'] = $productSku->price * $items[$id]['quantity'];
                $items[$id]['discount_amount'] = 0;
                $items[$id]['amount'] = $productSku->price;
            }
            $totalAmount = collect($items)->sum('total_amount'); // 订单原始总金额

            // 确认优惠券使用
            if ($coupon) {
                $discountAmount = $couponService->calcDiscountAmount($coupon, $totalAmount);
                if ($discountAmount > 0) {
                    // 具体优惠金额分配
                    $items = $couponService->allocateDiscount($items, $discountAmount);
                } else {
                    \Log::warning("无效的优惠券使用, 未产生减免费用", compact('coupon', 'totalAmount'));
                    $coupon = null;
                }
            }

            // 创建订单子记录
            $orderItems = collect();
            foreach ($productSkus as $productSku) {
                $productSkuId = $productSku->id;
                $requireQuantities = $items[$productSkuId]['quantity'];

                $orderItem = new OrderItem();
                $orderItem->forceFill([
                    'user_id' => $user->id,
                    'product_sku_id' => $productSku->id,
                    'product_id' => $productSku->product_id,

                    'quantity' => $requireQuantities,
                    'amount' => $requireQuantities * $productSku->price,
                    'paid_amount' => $items[$productSkuId]['total_amount'] - $items[$productSkuId]['discount_amount'],
                    'refund_status' => OrderItem::REFUND_STATUS_PENDING,
                    'refund_quantity' => 0,
                    'refund_amount' => 0,
                ]);

                $orderItems->push($orderItem);
            }

            // 创建订单主记录
            $order = new Order();
            $order->forceFill([
                'no' => static::genOrderNo(),
                'user_id' => $user->id,

                'status' => Order::ORDER_STATUS_CREATED,
                'amount' => $orderItems->sum('amount'),
                'paid_amount' => $orderItems->sum('paid_amount'),
                'address' => $address->only(['contact_name', 'contact_phone', 'zip', 'province', 'city', 'district', 'address']),
                'remark' => $remark,
            ]);

            // 健壮性判断
            if ($order->paid_amount < 0.01) {
                throw new InternalException(
                    "出现异常订单,优惠后金额小于 0.01. data: "
                    . json_encode(compact('order', 'orderItem', 'coupon', 'user'), JSON_UNESCAPED_UNICODE)
                );
            }

            // 更新地址最后使用时间
            $address->touch();

            // 优惠券使用并校验
            if ($coupon) {
                $order->coupon()->associate($coupon);
                if (!$coupon->use()) {
                    throw new InvalidRequestException("优惠券已兑换完毕");
                }
            }

            // 保存订单
            $order->save();
            $order->orderItems()->saveMany($orderItems);

            // 删除购物车相关商品
            /* @var CartService $cartService */
            $cartService = app(CartService::class);
            //TODO
            // $cartService->removeProductSkus($user, $productSkus);

            // 扣除库存
            foreach ($productSkus as $productSku) {
                //TODO
                // if (!$productSku->decreaseStock($items[$productSku->id]['quantity'])) {
                //     throw new InvalidRequestException("商品库存不足");
                // }
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

    /**
     * 更新订单状态为已支付
     *
     * @param Order $order
     * @param       $paymentMethod
     * @param       $paymentNo
     * @param       $paidAt
     *
     * @throws \Exception
     */
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
