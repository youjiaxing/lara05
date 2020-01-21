<?php
/**
 * @var \App\Models\OrderRefund $orderRefund
 * @var \App\Models\Order       $order
 */
?>

@extends("layouts.app")
@section("title", "")

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="float-left">退款单编号: {{ $orderRefund->no }}</div>
            <div class="float-right">订单编号: {{ $orderRefund->order->no }}</div>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <a name="" id="" class="btn btn-primary" href="{{ route('orders.show', [$orderRefund->order_id]) }}" role="button">返回订单</a>
            </div>

            <table class="table table-hover">
                <thead>
                <tr>
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
                            <div class="media">
                                <a class="d-flex" href="#">
                                    <img src="{{ $product->image_url }}" alt="" width="75px" height="75px">
                                </a>
                                <div class="media-body ml-3">
                                    <h5>{{ $product->title }}</h5>
                                    <div class="text-muted mt-2">{{ $productSku->title }}</div>
                                </div>
                            </div>
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
        </div>
        <div class="card-footer bg-white">
            <div class="d-table">
                <div class="d-table-row">
                    <div class="d-table-cell">总退款金额:</div>
                    <div class="d-table-cell">{{ $orderRefund->amount }}</div>
                </div>

                <div class="d-table-row">
                    <div class="d-table-cell">退款理由:</div>
                    <div class="d-table-cell">{{ $orderRefund->user_remark }}</div>
                </div>

                <div class="d-table-row">
                    <div class="d-table-cell">退款渠道:</div>
                    <div class="d-table-cell">原路返回</div>
                </div>

                <div class="d-table-row">
                    <div class="d-table-cell">状态:</div>
                    <div class="d-table-cell">{{ $orderRefund->status_map }}</div>
                </div>

                @if($orderRefund->status == \App\Models\OrderRefund::STATUS_SUCCESS)
                    <div class="d-table-row">
                        <div class="d-table-cell">退款时间:</div>
                        <div class="d-table-cell">{{ $orderRefund->refunded_at }}</div>
                    </div>
                @elseif ($orderRefund->status == \App\Models\OrderRefund::STATUS_REJECT)
                    <div class="d-table-row">
                        <div class="d-table-cell">拒绝理由:</div>
                        <div class="d-table-cell">{{ $orderRefund->reject_reason }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@stop

@section("script")

@stop

@section("style")

@stop
