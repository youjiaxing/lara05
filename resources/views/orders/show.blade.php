@extends("layouts.app")
@section("title", "订单详情")

@section("content")
    @php
        /* @var \App\Models\Order $order */
        $orderRefund = $order->last_order_refund;
    @endphp

    <div class="row text-center">
        {{--订单状态--}}
        <div class="col-sm-12">
            <div class="font-weight-bolder">
                <h3>订单状态: {{ $order->status_map }}</h3>

                @if (!$orderRefund)
                    <div class="alert alert-info" role="alert">
                        <strong>当前未发生退款</strong>
                    </div>
                @else
                    <div class="alert alert-info" role="alert">

                        @switch($orderRefund->status)
                            @case(\App\Models\OrderRefund::STATUS_CREATED)
                            <div>退款申请等待处理中.</div>

                            @break

                            @case(\App\Models\OrderRefund::STATUS_RETURN)
                            <div>退款申请已接受, 请尽快退回相关商品.</div>
                            @break

                            @case(\App\Models\OrderRefund::STATUS_REJECT)
                            <div>退款被拒绝</div>
                            <div>订单退款状态: {{ $order->refund_status_map }}</div>
                            @break

                            @case(\App\Models\OrderRefund::STATUS_SUCCESS)
                            <div>退款成功</div>
                            <div>订单退款状态: {{ $order->refund_status_map }}</div>
                            @break
                        @endswitch

                        <div>
                            详情请点击 <a href="{{ route('orderRefunds.show', ['order' => $order->id, 'order_refund' => $orderRefund->id]) }}">退款单</a> 查看
                        </div>
                    </div>
                @endif
            </div>

            {{--根据订单状态显示特有状态--}}
            @switch($order->status)
                {{--等待付款--}}
                @case(\App\Models\Order::ORDER_STATUS_CREATED)
                <a href="{{ route('payments.alipay', [$order->id]) }}" class="btn btn-primary">支付宝付款</a>
                @break

                {{--已付款, 等待发货--}}
                @case(\App\Models\Order::ORDER_STATUS_PAID)
                <a name="" id="" class="btn btn-danger" href="{{ route('orderRefunds.create', [$order]) }}" role="button">申请退款</a>
                @break

                {{--已发货--}}
                @case(\App\Models\Order::ORDER_STATUS_DELIVERED)
                <button type="button" class="btn btn-primary" id="received_btn">确认收货</button>
                <a name="" id="" class="btn btn-danger" href="{{ route('orderRefunds.create', [$order]) }}" role="button">申请退款</a>
                @break

                {{--已完成--}}
                @case(\App\Models\Order::ORDER_STATUS_RECEIVED)
                <a href="{{ route('orders.review', [$order->id]) }}" class="btn btn-success btn-sm">
                    @if ($order->isReviewed())
                        查看评价
                    @else
                        评价
                    @endif
                </a>
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
        <table class="table table-hover">
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
                            <a class="d-flex" href="{{ route('products.show', [$product->id]) }}">
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

    @if ($order->express_status != \App\Models\Order::EXPRESS_STATUS_PENDING)
        <hr>
        <div class="row mt-3">
            <div class="col-md-6 col-12">
                <h3>物流信息</h3>
                <div class="d-table">
                    <div class="d-table-row">
                        <div class="d-table-cell">物流公司</div>
                        <div class="d-table-cell">{{ $order->express_company }}</div>
                    </div>
                    <div class="d-table-row">
                        <div class="d-table-cell">物流单号</div>
                        <div class="d-table-cell">{{ $order->express_no }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <h3>物流跟踪信息</h3>
                当前暂不支持
            </div>
        </div>
    @endif
@stop

@section("script")
    <script>
        $(function () {
            $('#received_btn').on('click', function () {
                swal({
                    title: "确认已经收到商品?",
                    icon: "warning",
                    buttons: ["取消", "确认收货"],
                    dangerMode: true,
                }).then(function (confirm) {
                    console.log(confirm);
                    if (confirm) {
                        axios.post("{{ route('orders.receive', [$order->id]) }}")
                            .then(function (response) {
                                window.location.reload();
                            });
                    }
                });
            })
        });
    </script>
@stop
