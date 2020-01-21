<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRefundPartRequest;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\OrderRefundItem;
use App\Services\OrderRefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderRefundController extends Controller
{
    public function create(Order $order)
    {
        $this->authorize('own', $order);

        //TODO
        // 仅订单已支付才可申请
        // 未发货的必须整单取消, 无需显示该视图, 直接调用 refundAll 提交
        // 已发货的可以整单取消, 也可以部分取消, 部分取消时需要显示该视图
        // 已完成的不可以申请, 若需售后则联系客服处理, 由客服砸后台手动操作

        $order->load('orderItems.productSku.product');
        return view('refunds.create', ['order' => $order]);
    }

    public function show(Order $order, OrderRefund $orderRefund)
    {
        $this->authorize('own', $order);
        $this->authorize('own', $orderRefund);

        $orderRefund->load('orderRefundItems.productSku.product');

        return view('refunds.show', ['order' => $order, 'orderRefund' => $orderRefund]);
    }

    /**
     * 部分退款
     *
     * @param Order                  $order
     * @param OrderRefundPartRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function refundPart(Order $order, OrderRefundPartRequest $request)
    {
        $this->authorize('own', $order);

        $validated = $request->validated();
        $userRemark = $validated['user_remark'];
        $items = $validated['items'];

        $orderRefund = $orderRefund = $this->refund($order, $items, $userRemark);

        return response()->json([
            'id' => $orderRefund->id
        ]);
    }

    /**
     * 整单退款
     *
     * @param Order              $order
     * @param Request            $request
     * @param OrderRefundService $service
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function refundAll(Order $order, Request $request, OrderRefundService $service)
    {
        $this->authorize('own', $order);
        $validated = $request->validate(['user_remark' => ['required', 'string', 'between:1,255']]);

        // 构造所有可退款商品的列表
        $userRemark = $validated['user_remark'];
        $items = $service->generateAllRefundableItems($order);

        $orderRefund = $this->refund($order, $items, $userRemark);

        return response()->json([
            'id' => $orderRefund->id
        ]);
    }

    /**
     * @param Order  $order
     * @param array[]  $items = [
     *     'id' => 1,
     *     'quantity' => 1,
     * ]
     * @param string $userRemark
     * @param string $type
     *
     * @return |null
     * @throws InvalidRequestException
     */
    protected function refund(Order $order, array $items, string $userRemark, $type = OrderRefund::TYPE_REFUND)
    {
        // 订单状态需为: 已支付, 已发货
        if (empty($items) || !$order->isRefundable()) {
            \Log::debug("当前无可退款的项", compact('items', 'order'));
            throw new InvalidRequestException("当前无可退款的项");
        }

        // 退款类型
        switch ($order->status) {
            // "已支付" 退款类型必须是 仅退款
            case Order::ORDER_STATUS_PAID:
                if ($type !== OrderRefund::TYPE_REFUND) {
                    throw new InvalidRequestException("'已支付'状态的订单仅可选择'仅退款'");
                }
                break;

            // "已发货" 退款类型可以是 仅退款 或 退货
            // "确认收货" 退款类型可以是 仅退款 或 退货
            case Order::ORDER_STATUS_DELIVERED:
                break;


            default:
                throw new InvalidRequestException("该笔订单无法退款");
        }

        // 订单不得存在未处理的退款申请单
        if (!in_array($order->refund_status, [Order::REFUND_STATUS_PENDING, Order::REFUND_STATUS_REFUND_PART])) {
            throw new InvalidRequestException("已存在退款申请");
        }

        //TODO 该部分的主逻辑应挪到 OrderRefundService 中
        $orderRefund = null;
        $orderItems = $order->orderItems->keyBy('id');
        DB::transaction(function () use ($order, $items, $orderItems, $userRemark, $type, &$orderRefund) {
            $orderRefund = new OrderRefund();
            $orderRefund->forceFill([
                'no' => $order->no . "_" . now()->format("ymdHis"), // TODO 此处是随便搞的
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => 0,
                'status' => OrderRefund::STATUS_CREATED,
                'type' => $type,
                'user_remark' => $userRemark,
            ]);
            $orderRefund->save();

            $amount = 0;

            $refundItemsArr = [];
            foreach ($items as $item) {
                $orderItemId = $item['id'];
                $refundQuantity = $item['quantity'];
                $itemRefundAmount = $orderItems[$orderItemId]->unit_price * $refundQuantity;
                $amount += $itemRefundAmount;

                $refundItemsArr[] = [
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'order_item_id' => $orderItemId,
                    'product_sku_id' => $orderItems[$orderItemId]->product_sku_id,
                    'product_id' => $orderItems[$orderItemId]->product_id,

                    'quantity' => $refundQuantity,
                    'amount' => $itemRefundAmount,
                ];
            }

            $orderRefund->orderRefundItems()->createMany($refundItemsArr);

            $orderRefund->update([
                'amount' => $amount,
            ]);

            $order->refund_status = Order::REFUND_STATUS_APPLIED;
            $order->save();
        });

        return $orderRefund;
    }
}
