<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2020/2/9 14:53
 */

namespace App\Services;

use App\Exceptions\InternalException;
use App\Exceptions\InvalidCouponException;
use App\Exceptions\InvalidRequestException;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class CouponService
{
    /**
     * 校验优惠券是否可用
     *
     * @param Coupon     $coupon
     * @param null|float $amount
     *
     * @param User|null  $user
     *
     * @throws InvalidCouponException
     */
    public function checkValid(Coupon $coupon, $amount = null, User $user = null)
    {
        // 判断券的启用状态
        if (!$coupon->enabled) {
            throw new InvalidCouponException("无效的券", 404);
        }

        // 判断券的有效时间
        if ($coupon->not_before && $coupon->not_before->isFuture()) {
            throw new InvalidCouponException('未到使用时间', 403);
        }

        if ($coupon->not_after && $coupon->not_after->isPast()) {
            throw new InvalidCouponException('优惠券已过期', 403);
        }

        // 判断券是否用完了
        if ($coupon->used >= $coupon->total) {
            throw new InvalidCouponException('优惠券已兑换完毕', 403);
        }

        // 判断是否满足最低金额要求
        if (!is_null($amount) && $amount < $coupon->cond_min_amount) {
            throw new InvalidCouponException('不满足最低金额要求', 403);
        }

        // 优惠券重复使用判断
        if ($user && $user->orders()->where('status', '!=', Order::ORDER_STATUS_CLOSED)->where('coupon_id', $coupon->id)->exists()) {
            throw new InvalidCouponException('优惠券仅限使用一次', 403);
        }
    }

    /**
     * 根据订单金额计算可优惠金额
     *
     * @param Coupon    $coupon
     * @param float     $orderAmount 订单总金额
     *
     * @param User|null $user
     *
     * @return float    可减免金额
     * @throws InvalidCouponException
     */
    public function calcDiscountAmount(Coupon $coupon, $orderAmount, User $user = null)
    {
        $this->checkValid($coupon, $orderAmount, $user);

        if ($coupon->type == Coupon::TYPE_FIXED) {
            $discountAmount = $coupon->value;
        } else {
            $discountAmount = $coupon->value / 100.0 * $orderAmount;
            if ($coupon->max_discount_amount && $coupon->max_discount_amount < $discountAmount) {
                $discountAmount = $coupon->max_discount_amount;
            }
        }

        if ($discountAmount >= $orderAmount) {
            $discountAmount = $orderAmount - 0.01;
        }

        return floor($discountAmount * 100.0) / 100.0;
    }

    /**
     * 分配优惠金额, 会为每一项新增一个字段 'discount_amount'
     *
     * @param array $items [
     *     '123456' => [
     *          'amount' => 2,
     *          'total_amount' => 10,
     *          'quantity' => 5,
     *          'product_sku_id' => 123456,
     *          'discount_amount' => 0,         // 该项所分配的优惠金额
     *     ]
     * ]
     *
     * @return array
     */
    public function allocateDiscount(array $items, $discountAmount)
    {
        $discountAmount = (float)$discountAmount;
        $totalAmount = (float)collect($items)->sum('total_amount');
        if ($discountAmount >= $totalAmount) {
            throw new InvalidRequestException("优惠金额错误");
        }

        $allocatedDiscountAmount = 0;
        foreach ($items as $key => &$value) {
            $value['discount_amount'] = floor($discountAmount * $value['total_amount'] / $totalAmount * 100.0) / 100;   // 保留2位小数, 舍去多余的位数
            $allocatedDiscountAmount += $value['discount_amount'];
        }

        // 健壮性判断
        if ($allocatedDiscountAmount > $discountAmount) {
            throw new InternalException(sprintf("优惠金额分配异常, 分配金额(%s)超过优惠金额(%s)", $allocatedDiscountAmount, $discountAmount), compact('items'));
        }

        // 需要再分配
        $hasZeroAmountAllocate = false;
        if ($allocatedDiscountAmount != $discountAmount) {
            foreach ($items as $key => &$value) {
                // min(还需分配的金额, 该商品可继续优惠的金额)
                $addition = min($discountAmount - $allocatedDiscountAmount, $value['total_amount'] - $value['discount_amount']);
                if ($addition > 0) {
                    $value['discount_amount'] += $addition;
                    $allocatedDiscountAmount += $addition;

                    if ($value['total_amount'] == $value['discount_amount']) {
                        $hasZeroAmountAllocate = true;
                    }
                }

                if ($allocatedDiscountAmount >= $discountAmount) {
                    break;
                }
            }
        }

        // 记录特殊金额
        if ($hasZeroAmountAllocate) {
            \Log::info("订单分配出现某些商品实际需支付费用位0", compact('items', 'discountAmount', 'totalAmount'));
        }

        return $items;
    }
}
