<?php
/**
 * @var \App\Models\Order $order
 */
?>
@extends("layouts.app")
@section("title", "")

@section("content")
    <div class="row">
        <div class="col-12">
            <form method="post" action="#">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th></th>
                        <th>商品</th>
                        <th>数量</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($order->orderItems as $orderItem)
                        <?php
                        /**
                         * @var \App\Models\OrderItem $orderItem
                         */
                        $productSku = $orderItem->productSku;
                        $product = $productSku->product;
                        ?>
                        <tr>
                            <td scope="row">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="" id="" value="checkedValue"
                                               @if($orderItem->isClosed()) disabled @endif>
                                        &nbsp;
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="media">
                                    <a class="d-flex" href="{{ route('products.show', [$product->id]) }}">
                                        <img src="{{ $product->image_url }}" alt="" width="75px" height="75px">
                                    </a>
                                    <div class="media-body ml-3">
                                        <h5>{{ $product->title }}</h5>
                                        <div class="mt-2">{{ $productSku->title }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php($quantityName = "refund[{$orderItem->id}][quantity]")
                                <div class="form-group">
                                    <input type="number" class="form-control form-control-sm w-25" name=""
                                           id="{{ $quantityName }}" placeholder="" @if($orderItem->isClosed()) disabled @endif min="1"
                                           max="{{ $orderItem->validQuantity() }}" value="{{ $orderItem->validQuantity() }}" data-id="{{ $orderItem->id }}">
                                    <label for="{{ $quantityName }}" class="col-form-label-sm text-danger">最大可退款数量: {{ $orderItem->validQuantity() }}</label>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="form-group mt-4">
                    <label for="">退款原因:</label>
                    <textarea class="form-control" name="" id="user_remark" rows="3" maxlength="255" required></textarea>
                    <small class="text-muted float-right">最大字数255</small>
                </div>

                <div class="text-right pt-4">
                    <button type="button" class="btn btn-danger" id="refund_btn">申请退款</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section("script")
    <script>
        $(() => {
            $('#refund_btn').on('click', () => {
                let checked = $('input:checked');
                if (checked.length == 0) {
                    swal({
                        title: "请选择需要退款的商品",
                        icon: "error",
                    });
                    return;
                }
                let user_remark = $('#user_remark').val();
                if (user_remark.length > 255 || user_remark.length === 0) {
                    swal("请填写退货原因", "", "error");
                    return;
                }

                let data = {
                    'user_remark': user_remark,
                    'items': [],
                };
                checked.each((index, element) => {
                    let input = $(element).closest('tr').find('input[type="number"]');
                    let id = input.data('id');
                    let quantity = input.val();

                    // console.log($(element).closest('tr'));
                    // console.log(element, $(this).closest('tr'), input, id, quantity);

                    if (quantity < 1) {
                        swal("退款的商品数量错误", "", "error");
                        return false;
                    }

                    data['items'].push({
                        id: id,
                        quantity: parseInt(quantity),
                    });
                });

                console.log(data);
                swal({
                    title: '确认申请退款?',
                    icon: "info",
                    buttons: ["取消", "确定"],
                    dangerMode: true
                })
                    .then((confirm) => {
                        if (!confirm) {
                            return;
                        }

                        axios.post("{{ route('orderRefunds.refundPart', [$order->id]) }}", data)
                            .then(response => {
                                swal("成功申请, 请等待审核", "", "info")
                                    .then(() => {
                                        let refundId = response.data.id;
                                        window.location = "/refunds/{{ $order->id }}/" + refundId;
                                    })
                            })
                    });
            })
        })
    </script>
@stop

@section("style")

@stop
