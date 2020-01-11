@extends("layouts.app")
@section("title", "订单列表")

@section("content")
    <?php /* @var \Illuminate\Pagination\Paginator $orders */ ?>
    <div class="row row-cols-1">
        @foreach($orders as $order)
            <?php /* @var \App\Models\Order $order */ ?>
            <div class="col @if(!$loop->first) mt-4 @endif">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            订单号: {{ $order->no }}
                        </div>
                        <div class="float-right">
                            {{ $order->created_at->toDateTimeString() }}
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>商品详情</th>
                                <th>单价</th>
                                <th>数量</th>
                                <th>订单总价</th>
                                <th class="text-center">状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order->orderItems as $orderItem)
                                @php
                                    /* @var \App\Models\OrderItem $orderItem */
                                    $productSku = $orderItem->productSku;
                                    $product = $productSku->product;
                                @endphp
                                <tr>
                                    <td scope="row" class="td-product-title">
                                        <div class="media">
                                            <img src="{{ $product->image_url }}" alt="" class="align-self-center mr-3" width="75px"
                                                 height="75px">
                                            <div class="media-body">
                                                <p>{{ $product->title }}</p>
                                                <p class="text-secondary">{{ $productSku->title }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fa fa-rmb" aria-hidden="true"></i> {{ $productSku->price }}
                                    </td>
                                    <td>
                                        <div class="text-secondary">x{{ $orderItem->quantity }}</div>
                                    </td>

                                    {{-- 只显示一次 --}}
                                    @if ($loop->first)
                                        <td rowspan="{{ count($order->orderItems) }}">
                                            <div class="text-secondary">总额 <i class="fa fa-rmb" aria-hidden="true"></i> {{ $order->paid_amount }}</div>
                                            @if ($order->isPaid())
                                                <div class="text-secondary border-top">{{ $order->payment_method }}</div>
                                            @endif
                                        </td>
                                        <td rowspan="{{ count($order->orderItems) }}" class="text-center">
                                            <div class="">{{ $order->status_map }}</div>
                                            @if ($order->status == \App\Models\Order::ORDER_STATUS_CREATED)
                                                <div class="">请于 {{ $order->created_at->addSeconds(config('shop.order_ttl'))->toTimeString("minute") }}前完成支付
                                                </div>
                                                <div class="">否则订单将自动关闭</div>
                                            @endif
                                        </td>
                                        <td rowspan="{{ count($order->orderItems) }}">
                                            <a href="{{ route('orders.show', [$order->id]) }}" class="btn btn-primary btn-sm">查看详情</a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row d-block clearfix pagination-section">
        <div class="float-right mb-5 mt-3">
            {{ $orders->links() }}
        </div>
    </div>


@stop

@section("style")
    <style>
        .td-product-title {
            width: 40%;
        }

        .pagination-section {
            padding-left: 15px;
            padding-right: 15px;
        }
    </style>
@stop
