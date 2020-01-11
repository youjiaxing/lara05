@extends("layouts.app")
@section("title", "购物车")

@section("content")
    <div class="row">
        <table class="table table-borderless">
            <thead>
            <tr>
                <th class="text-left" scope="col"><input type="checkbox" id="select_all_btn" class="mr-2 text-left">全选</th>
                <th class="text-center" scope="col">商品</th>
                <th class="text-center" scope="col">单价</th>
                <th class="text-center" scope="col">数量</th>
                <th class="text-center" scope="col">小计</th>
                <th class="text-center" scope="col">操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($carts as $cart)
                <tr class="cart-item" data-id="{{ $cart->productSku->id }}" data-cart-id="{{ $cart->id }}">
                    <td class="text-left" scope="row"><input type="checkbox" name="selected" {{ $cart->is_sale && $cart->hasStock() ? "checked" : "disabled" }}>
                    </td>
                    <td class="text-center">
                        <div class="media">
                            <a class="mr-4" href="#">
                                <img src="{{ $cart->productSku->product->imageUrl }}" alt="" class="product-img">
                            </a>
                            <div class="media-body text-left">
                                <h5 class="{{ ($cart->is_sale and $cart->hasStock()) ? "" : "text-delete" }}">{{ $cart->productSku->product->title }}</h5>
                                @if (!$cart->is_sale)
                                    <div class="text-danger font-weight-bold">商品已下架</div>
                                @elseif (!$cart->hasStock())
                                    <div class="text-danger font-weight-bold">库存不足</div>
                                @endif

                            </div>
                            <div class="text-secondary">{{ $cart->productSku->title }}</div>
                        </div>
                    </td>
                    <td class="text-center">{{ $cart->productSku->price }}</td>
                    <td class="text-center"><input name="amount" type="number" min="1" value="{{ $cart->amount }}" class="form-control form-control-sm"></td>
                    <td class="text-center"><i class="fa fa-rmb" aria-hidden="true"></i> <span
                            class="font-weight-bold">{{ $cart->productSku->price * $cart->amount }}</span></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm delete-product-btn">删除</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="row">
        <form class="w-100">
            <div class="form-group row">
                <label class="col-sm-2 text-right control-label" for="">选择收货地址</label>
                <div class="col-sm-8">
                    <select class="form-control" name="address" id="">
                        @foreach(user()->addresses as $address)
                            <option value="{{ $address->id }}" @if ($loop->first) "selected" @endif>
                            {{ $address->fullAddress }} {{ $address->contact_name }} {{ $address->contact_phone }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="" class="col-sm-2 text-right control-label">备注</label>
                <div class="col-sm-8">
                    <textarea class="form-control" name="remark" id="" rows="3"></textarea>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-8 offset-sm-2">
                    <button type="button" class="btn btn-primary" id="submit_order_btn">提交订单</button>
                </div>
            </div>
        </form>
    </div>
@stop

@section("script")
    <script>
        $('#select_all_btn').on('click', function (event) {
            $('input[type=checkbox][name="selected"]:enabled').prop('checked', $(this).prop('checked'));
        });

        $('.delete-product-btn').on('click', function () {
            let parent_tr = $(this).closest("tr");
            let cart_id = parent_tr.data('cart-id');

            swal({
                title: "确定从购物车删除?",
                icon: "warning",
                dangerMode: true,
                buttons: true,
            }).then(function (confirm) {
                if (!confirm) {
                    return;
                }

                axios.delete("/carts/" + cart_id)
                    .then(function () {
                        swal("删除成功", "", "success").then(function () {
                            parent_tr.remove();
                        });
                    });
            });
        });

        $('#submit_order_btn').on('click', function (event) {
            var req = {
                remark: $('textarea[name=remark]').val(),
                address_id: $('select[name=address]').val(),
                items: []
            };
            $('input[type=checkbox][name="selected"]:enabled:checked').parent().parent().each(function (index, element) {
                let product_sku_id = this.dataset.id;
                let amount = $(this).find('input[name=amount]').val();

                if (amount <= 0) {
                    return;
                }

                req.items.push({
                    product_sku_id: product_sku_id,
                    amount: amount,
                });
            });

            if (req.items.length === 0) {
                swal("请选择要购买的商品", "", "warning");
                return;
            }

            console.log(req);
            axios.post("{{ route('orders.store') }}", req)
                .then(function (response) {
                    swal("购买成功", "", "success")
                        .then(function () {
                            response_data = response.data;
                            if (response_data.redirect) {
                                window.location = response_data.redirect;
                            } else if (response.data.order_id) {
                                window.location = "/orders/" + response.data.order_id;
                            } else {
                                window.location = "/orders";
                            }
                        });
                });
        });
    </script>
@stop

@section("style")
    <style>
        .product-img {
            width: 80px;
            height: 60px;
        }
    </style>
@stop
