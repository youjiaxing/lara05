<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2020/1/20 15:15
 */

namespace App\Services;

use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use App\Models\OrderRefund;
use Illuminate\Support\Facades\DB;

class OrderRefundService
{
    /**
     * 生成订单可退款的项
     *
     * @param Order $order
     *
     * @return array[] = [
     *     'id' => 1,
     *     'quantity' => 1,
     * ]
     */
    public function generateAllRefundableItems(Order $order)
    {
        $resp = [];

        if (!$order->isRefundable()) {
            return $resp;
        }

        foreach ($order->orderItems as $orderItem) {
            if ($orderItem->validQuantity() <= 0 || $orderItem->paid_amount <= $orderItem->refund_amount) {
                continue;
            }

            $resp[] = [
                'id' => $orderItem->id,
                'quantity' => $orderItem->validQuantity(),
            ];
        }

        return $resp;
    }

    /**
     * 拒绝退款
     *
     * @param OrderRefund $orderRefund
     * @param string      $rejectReason
     *
     * @throws InvalidRequestException
     */
    public function rejectRefund(OrderRefund $orderRefund, $rejectReason = "")
    {
        if (!in_array($orderRefund->status, [OrderRefund::STATUS_CREATED, OrderRefund::STATUS_RETURN])) {
            throw new InvalidRequestException("退款申请单状态错误");
        }

        DB::transaction(function () use ($rejectReason, $orderRefund) {
            $orderRefund->forceFill([
                'status' => OrderRefund::STATUS_REJECT,
                'reject_reason' => $rejectReason,
            ]);

            $order = $orderRefund->order;
            $order->refund_status = $order->detectRefundStatus();
            $order->save();
            $orderRefund->save();
        });
    }

    public function acceptRefund(OrderRefund $orderRefund)
    {
        $order = $orderRefund->order;
        if ($order->refund_amount + $orderRefund->amount > $order->paid_amount) {
            throw new InvalidRequestException("总退款金额超出订单实际支付金额, 请检查是否出错.");
        }

        // 以下出错时, 可以考虑将出错信息保存到 OrderRefund 的 extra 数据中
        switch ($order->payment_method) {
            case Order::PAYMENT_ALIPAY:
                // 重复退款时也是返回成功, 但表示资金是否发生变化的参数值是 "N", 而非 "Y"
                $ret = $this->refundViaAlipay($order, $orderRefund);
                break;

            default:
                throw new InvalidRequestException("未知的支付来源: " . $order->payment_method);
        }


        DB::transaction(function () use ($orderRefund, $order) {
            $orderRefund->load('orderRefundItems.orderItem');
            $orderRefund->status = OrderRefund::STATUS_SUCCESS;
            $orderRefund->refunded_at = now();

            // 更新子订单信息
            foreach ($orderRefund->orderRefundItems as $orderRefundItem) {
                $orderItem = $orderRefundItem->orderItem;
                $orderItem->refund_amount += $orderRefundItem->amount;
                $orderItem->refund_quantity += $orderRefundItem->quantity;
                $orderItem->refund_status = $orderItem->detectRefundStatus();
                $orderItem->save();
            }

            // 更新订单信息
            $order->refund_amount += $orderRefund->amount;
            if ($order->refund_amount == $order->paid_amount) {
                $order->status = Order::ORDER_STATUS_CLOSED;
            }
            $order->refund_status = $order->detectRefundStatus();   // 需放到最后

            $order->save();
            $orderRefund->save();
        });

        //TODO 恢复商品库存(这个不急, 可以丢队列里)
        //如果未发货, 则可以直接恢复库存
        //如果已发货, 则不改变库存, 由运营人员手动处理.

    }

    /**
     * @param Order       $order
     * @param OrderRefund $orderRefund
     *
     * @return \Yansongda\Supports\Collection
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    protected function refundViaAlipay(Order $order, OrderRefund $orderRefund)
    {
        // 支付宝文档: https://docs.open.alipay.com/api_1/alipay.trade.refund
        $ret = app('alipay')->refund([
            'out_trade_no' => $order->no,   // 我方订单编号
            'refund_amount' => $orderRefund->amount,    // 退款金额, 元
            'out_request_no' => $orderRefund->no,   // 退款交易编号
        ]);

        \Log::debug("refundViaAlipay 支付宝退款返回结果", $ret->toArray());

        // 存在 sub_code 时表示出错
        if (!empty($ret['sub_code'])) {
            throw new \Exception("退款出错, 错误码: " . $ret['sub_code']);
        }

        if ($ret['fund_change'] != 'Y') {
            \Log::warning("本次退款资金未发生变化", $ret->toArray());
        }
        return $ret;
    }
}
