<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\OrderItem;

class OrderRefundPartRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_remark' => ['required', 'string', 'between:1,255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.id' => ['required', 'integer', 'min:1'],
            'items' => ['required', 'array', function ($attribute, $value, $fail) {
                $orderItemIds = collect($value)->pluck('quantity', 'id');
                $orderItems = OrderItem::query()->whereIn('id', $orderItemIds->keys())->get()->keyBy('id');
                if ($orderItems->count() != $orderItemIds->count()) {
                    $fail("退款商品数目与订单不匹配");
                }

                $orderIds = $orderItems->pluck('order_id')->unique();
                if ($orderIds->count() !== 1) {
                    $fail("单次仅可对同一笔订单中的商品进行退款.");
                }
                $order = $this->route('order');
                /* @var Order $order */
                if ($order->id != $orderIds->first()) {
                    $fail("退款商品与订单不匹配");
                }

                // 校验可退款数量
                foreach ($orderItemIds as $orderItemId => $refundQuantity) {
                    /* @var OrderItem $orderItem */
                    $orderItem = $orderItems->get($orderItemId);
                    if ($orderItem->validQuantity() < $refundQuantity) {
                        $fail("订单中部分商品可退款数量不足");
                    }
                }
            }],
        ];
    }

    public function attributes()
    {
        return [
            'user_remark' => '退款原因',
            'items.*.quantity' => '退款商品数量',
            'items.*.id' => '退款商品'
        ];
    }


}
