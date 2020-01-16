<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
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

        $order = $orderService->store(
            user(),
            $address,
            $input['items'],
            empty($input['remark']) ? "" : $input['remark']
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
}
