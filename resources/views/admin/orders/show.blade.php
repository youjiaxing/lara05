<?php
/* @var \App\Models\Order $order */
?>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">订单流水号: {{ $order->no }}</h3>
        <div class="box-tools pull-right">
            <a href="{{ route('admin.products.index') }}" class="btn btn-default btn-xs"><i class="fa fa-list"></i> 列表</a>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-hover table-bordered">
            <tbody>
            <tr>
                <th>买家</th>
                <td>{{ $order->user->name }}</td>
                <th>支付时间</th>
                <td>{{ $order->paid_at }}</td>
            </tr>
            <tr>
                <th>支付方式</th>
                <td>{{ $order->payment_method }}</td>
                <th>支付渠道单号</th>
                <td>{{ $order->payment_no }}</td>
            </tr>
            <tr>
                <th>收货地址</th>
                <td colspan="3">{{ $order->full_address_with_contact }}</td>
            </tr>
            <tr>
                <th rowspan="{{ count($order->orderItems) + 1 }}">商品列表</th>
                <th>商品名称</th>
                <th>单价</th>
                <th>数量</th>
            </tr>
            @foreach($order->orderItems as $orderItem)
                @php
                    /* @var \App\Models\OrderItem $orderItem */
                    $productSku = $orderItem->productSku;
                    $product = $productSku->product;
                @endphp

                <tr>
                    <td>{{ $product->title }} - {{ $productSku->title }}</td>
                    <td>{{ $orderItem->unit_price }}</td>
                    <td>{{ $orderItem->quantity }}</td>
                </tr>
            @endforeach
            <tr>
                <th>订单金额</th>
                <td>{{ $order->paid_amount }}</td>
                <th>有效金额</th>
                <td>{{ $order->paid_amount - $order->refund_amount }}</td>
            </tr>
            <tr>
                <th>物流状态</th>
                <td>{{ $order->express_status_map }}</td>
            </tr>
            @if (!empty($order->express_data))
                <tr>
                    <th>物流公司</th>
                    <td>{{ $order->express_company }}</td>
                    <th>物流单号</th>
                    <td>{{ $order->express_no }}</td>
                </tr>
            @endif

            @if ($order->isPaid())
                <tr>
                    <td colspan="4">
                        <form action="{{ route('admin.orders.express', [$order->id]) }}" method="post" role="form" class="form-inline">
                            {{ csrf_field() }}

                            <div class="form-group @if($errors->has('express_company')) has-error @endif">
                                <label for="">物流公司</label>
                                <input type="text" class="form-control" required name="express_company" id="" placeholder="输入物流公司">

                                @foreach($errors->get('express_company') as $msg)
                                    <span class="help-block">{{ $msg }}</span>
                                @endforeach
                            </div>
                            <div class="form-group @if($errors->has('express_no')) has-error @endif" style="margin-left: 20px; margin-right: 20px">
                                <label for="">物流单号:</label>
                                <input type="text" name="express_no" id="" required class="form-control" value="" placeholder="输入物流单号">
                                @foreach($errors->get('express_no') as $msg)
                                    <span class="help-block">{{ $msg }}</span>
                                @endforeach
                            </div>

                            <button type="submit" class="btn btn-primary">@if (!empty($order->express_data)) 更新 @else 发货 @endif</button>
                        </form>
                    </td>
                </tr>
            @endif


            <tr>
                <th>退款状态</th>
                <td>{{ $order->refund_status_map }}</td>
                <th>累计退款申请单</th>
                <td>{{ count($order->orderRefunds) }}</td>
            </tr>

            </tbody>
        </table>
    </div>
</div>


<?php
$orderRefund = $order->active_order_refund;

if ($orderRefund) {
    // \Log::debug("orderRefund", $orderRefund->toArray());
    $orderRefund->load('orderRefundItems');
}
?>
@if ($orderRefund)

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">退款处理: {{ $orderRefund->no }}</h3>
        </div>
        <div class="box-body">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>商品图片</th>
                    <th>商品</th>
                    <th>退款数量</th>
                    <th>退款金额</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orderRefund->orderRefundItems as $orderRefundItem)
                    <?php
                    /**
                     * @var \App\Models\OrderRefundItem $orderRefundItem
                     */
                    $productSku = $orderRefundItem->productSku;
                    $product = $productSku->product;
                    ?>
                    <tr>
                        <td scope="row">
                            <a href="{{ route('products.show', [$product->id]) }}" target="_blank"><img src="{{ $product->image_url }}" alt="" width="75px"
                                                                                                        height="75px"></a>
                        </td>
                        <td>
                            <h5>{{ $product->title }}</h5>
                            <div class="text-muted mt-2">{{ $productSku->title }}</div>
                        </td>
                        <td>
                            {{ $orderRefundItem->quantity }}
                        </td>
                        <td>
                            {{ $orderRefundItem->amount }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="margin-top: 1.5em">总退款金额: {{ $orderRefund->amount }}</div>


            <div style="margin-top: 1.5em">
                <div>
                    @if ($orderRefund->status == \App\Models\OrderRefund::STATUS_CREATED)
                        <button class="btn btn-primary" id="refund_reject_btn">拒绝</button>
                        <button class="btn btn-success" id="refund_accept_btn">同意退款</button>
                    @elseif ($orderRefund->status == \App\Models\OrderRefund::STATUS_RETURN)
                        <button class="btn btn-primary" id="refund_reject_btn">取消并拒绝</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
<script>
    $(function () {
        @if ($orderRefund)
        $('#refund_reject_btn').on('click', () => {
            swal({
                title: "拒绝退款",
                // text: "请输入拒绝理由",
                type: "warning",
                // showCancelButton: true,
                confirmButtonText: "拒绝退款",
                // cancelButtonText: "关闭",
                // confirmButtonColor: '#3085d6',
                // cancelButtonColor: '#d33',
                // confirmButtonText: "",
                input: "textarea",
                inputPlaceholder: "请输入拒绝理由",
                showLoaderOnConfirm: true,
                // allowOutsideClick: false,
                preConfirm: (reject_reason) => {
                    console.log("input:" + reject_reason);
                    return $.ajax({
                        type: 'POST',
                        url: '{{ route('admin.orderRefund.reject', [$orderRefund->id]) }}',
                        data: JSON.stringify({
                            _token: LA.token,
                            reject_reason: reject_reason
                        }),
                        contentType: "application/json",
                    });
                },
            }).then((ret) => {
                if (ret.dismiss) {
                    return
                }
                // ret =  {dismiss: "overlay"}  点击空白处取消时
                // ret = {dismiss: "cancel"}    点击取消按钮时
                // ret = {value: {服务器返回的数据}}    点击确定时, 由 preConfirm 传来的值

                console.log("then:", ret);
                swal("拒绝退款成功", "", "success")
                    .then(() => {
                        // window.location.reload();
                        $.pjax({
                            url: window.location.href,
                            container: "#pjax-container",
                        });
                    })
            }).catch((error) => {
                console.log("catch:", error);
                swal("拒绝退款失败", error.responseJSON.message, "error");
            });
        });

        $('#refund_accept_btn').on('click', () => {
            swal({
                title: "同意退款",
                text: "本次退款总金额: {{ $orderRefund->amount }}",
                type: "warning",
                confirmButtonText: "同意",
                showCancelButton: true,
                cancelButtonText: "关闭窗口",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: (sth) => {
                    // sth = true

                    return $.ajax({
                        type: 'POST',
                        url: '{{ route('admin.orderRefund.accept', [$orderRefund->id]) }}',
                        data: JSON.stringify({
                            _token: LA.token,
                        }),
                        contentType: "application/json",
                    });
                },
            }).then((ret) => {
                if (ret.dismiss) {
                    return;
                }

                // ret = {value: true}

                console.log("then: ", ret);
                swal("退款成功", "", "success")
                    .then(() => {
                        $.pjax({
                            url: window.location.href,
                            container: "#pjax-container",
                        });
                    });
            }).catch((error) => {
                console.log("catch:", error);
                swal("退款失败", error.responseJSON.message, "error");
            });
        });
        @endif
    })
</script>


