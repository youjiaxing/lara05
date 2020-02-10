<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Events\OrderReviewed;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderReviewRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserAddress;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $input = $request->validated();
        $address = UserAddress::query()->findOrFail($input['address_id']);
        $coupon = !empty($input['coupon']) ? Coupon::query()->where('code', $input['coupon'])->firstOrFail() : null;

        $items = collect();
        foreach ($input['items'] as $item) {
            $items->push([
                'quantity' => $item['amount'],
                'product_sku_id' => $item['product_sku_id'],
            ]);
        }

        $order = $orderService->store(
            user(),
            $address,
            $items,
            empty($input['remark']) ? "" : $input['remark'],
            $coupon,
        );

//        return response()->json(['order_id' => $order->id, 'redirect' => route('orders.show', [$order->id])], Response::HTTP_CREATED);
        return response()->json(['order_id' => $order->id], Response::HTTP_CREATED);
    }

    public function index(Request $request)
    {
        $orders = user()->orders()->orderByDesc('id')->with(['orderItems.productSku.product'])->paginate();
        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        $this->authorize('own', $order);
        $order->load('orderItems.productSku.product');

        return view('orders.show', ['order' => $order]);
    }

    /**
     * 确认收货
     *
     * @param Order $order
     *
     * @return Order
     * @throws InvalidRequestException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function receive(Order $order)
    {
        $this->authorize('own', $order);

        if ($order->status != Order::ORDER_STATUS_DELIVERED) {
            throw new InvalidRequestException("订单状态错误");
        }

        $order->status = Order::ORDER_STATUS_RECEIVED;
        $order->express_status = Order::EXPRESS_STATUS_RECEIVED;
        //TODO 可能要记录下订单确认收货时间
        $order->save();
        return $order;
    }

    public function reviewShow(Order $order)
    {
        $this->authorize('own', $order);
        $order->load('orderItems.productSku.product');
        return view('orders.review', ['order' => $order]);
    }

    /**
     * 对一个订单中的所有商品一次性全部评价
     *
     * @param Order              $order
     * @param OrderReviewRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws InvalidRequestException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function reviewStore(Order $order, OrderReviewRequest $request)
    {
        $this->authorize('own', $order);

        // 当前暂不支持部分评价
        if ($order->review_status != Order::REVIEW_STATUS_PENDING) {
            throw new InvalidRequestException("请勿重复评价");
        }

        if ($order->status != Order::ORDER_STATUS_RECEIVED) {
            throw new InvalidRequestException("订单状态错误");
        }

        $input = $request->validated();
        // \Log::debug(__METHOD__, $data);

        $now = now();

        $order->review_status = Order::REVIEW_STATUS_ALL;
        foreach ($order->orderItems as $orderItem) {
            $orderItem->review_content = $input['reviews'][$orderItem->id]['content'];
            $orderItem->review_rating = $input['reviews'][$orderItem->id]['rating'];
            $orderItem->reviewed_at = $now;
        }

        DB::transaction(function () use ($order) {
            if (!$order->push()) {
                throw new \Exception("save error");
            }
        });

        OrderReviewed::dispatch($order);

        return back();
    }
}
