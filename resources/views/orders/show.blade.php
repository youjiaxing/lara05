@extends("layouts.app")
@section("title", "订单详情")

@section("content")
    @php /* @var \App\Models\Order $order */ @endphp

    <div class="row text-center">
        {{--订单状态--}}
        <div class="col-sm-12">
            <div class="font-weight-bolder">
                <h3>{{ $order->status_map }}</h3>
                @if ($order->refund_status != \App\Models\Order::REFUND_STATUS_PENDING)
                    <h4>{{ $order->refund_status_map }}</h4>
                    @endif
            </div>

            @switch($order->status)
                {{--等待付款--}}
                @case(\App\Models\Order::ORDER_STATUS_CREATED)
                <button type="button" class="btn btn-primary">付款</button>
                @break

                {{--已付款, 等待发货--}}
                @case(\App\Models\Order::ORDER_STATUS_PAID)
                <a name="" id="" class="btn btn-danger" href="#" role="button">申请退款</a>
                @break

                {{--已发货--}}
                @case(\App\Models\Order::ORDER_STATUS_DELIVERED)
                <button type="button" class="btn btn-primary">确认收货</button>
                <a name="" id="" class="btn btn-danger" href="#" role="button">申请退款</a>
                @break

                {{--已完成--}}
                @case(\App\Models\Order::ORDER_STATUS_RECEIVED)

                @break

                {{--已关闭(订单未完成)--}}
                @case(\App\Models\Order::ORDER_STATUS_CLOSED)

                @break
            @endswitch
        </div>
    </div>

    <div class="row mt-5">
        {{--收货信息--}}
        <div class="col-sm-6">
            <h3>收货人信息</h3>
            <div class="row">
                <div class="col-sm-3">收货人:</div>
                <div class="col-sm-9">{{ $order->address['contact_name'] }}</div>
            </div>
            <div class="row">
                <div class="col-sm-3">地址:</div>
                <div class="col-sm-9">{{ $order->full_address }}</div>
            </div>
            <div class="row">
                <div class="col-sm-3">手机号码:</div>
                <div class="col-sm-9">{{ $order->address['contact_phone'] }}</div>
            </div>
            <div class="row">
                <div class="col-sm-3">备注:</div>
                <div class="col-sm-9">{{ $order->remark }}</div>
            </div>
        </div>

        {{--付款信息--}}
        <div class="col-sm-6">
            <h3>付款信息</h3>
            <div class="row">
                <div class="col-sm-3">订单号</div>
                <div class="col-sm-9">{{ $order->no }}</div>
            </div>
            <div class="row">
                <div class="col-sm-3">商品总额</div>
                <div class="col-sm-9"><i class="fa fa-rmb" aria-hidden="true"></i> {{ $order->amount }}</div>
            </div>
            <div class="row">
                <div class="col-sm-3">优惠</div>
                <div class="col-sm-9"><i class="fa fa-rmb" aria-hidden="true"></i> {{ $order->paid_amount - $order->amount }}</div>
            </div>
            <div class="row">
                <div class="col-sm-3">应付总额</div>
                <div class="col-sm-9"><i class="fa fa-rmb" aria-hidden="true"></i> {{ $order->paid_amount }}</div>
            </div>

            @if($order->isPaid())
                <div class="row">
                    <div class="col-sm-3">支付渠道</div>
                    <div class="col-sm-9">{{ $order->payment_method }}</div>
                </div>
                <div class="row">
                    <div class="col-sm-3">第三方订单号</div>
                    <div class="col-sm-9">{{ $order->payment_no }}</div>
                </div>
            @elseif ($order->status == \App\Models\Order::ORDER_STATUS_CREATED)

            @endif
        </div>
    </div>

    {{--包含的所有商品--}}
    <div class="row mt-4">
        <table class="table">
            <thead>
            <tr>
                <th>商品</th>
                <th>单价</th>
                <th>数量</th>
                <th>合计</th>
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
                    <td scope="row" style="width: 40%">
                        <div class="media">
                            <a class="d-flex" href="#">
                                <img src="{{ $product->image_url }}" alt="" width="75px" height="75px">
                            </a>
                            <div class="media-body">
                                <p>{{ $product->title }}</p>
                                <p class="text-secondary">{{ $productSku->title }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <i class="fa fa-rmb" aria-hidden="true"></i> {{ $orderItem->unit_price }}
                    </td>
                    <td>
                        {{ $orderItem->quantity }}
                    </td>
                    <td>
                        <i class="fa fa-rmb" aria-hidden="true"></i> {{ $orderItem->amount }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop

@section("script")

@stop
