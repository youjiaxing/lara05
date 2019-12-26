@extends("layouts.app")
@section("title", "商品详情")

@section("content")
    <div class="row">
        <div class="col-sm-4 pl-0">
            <img src="{{ $product->imageUrl }}" alt="" class="img-responsive img-fluid">
        </div>

        <div class="col-sm-8 pr-0">
            <h2>{{ $product->title }}</h2>

            <div>
                <div class="row price-section mt-1 mb-3">
                    <div class="col-md-1">
                        价格
                    </div>
                    <div class="col-md-11 text-danger font-weight-bold">
                        <i class="fa fa-rmb" aria-hidden="true"></i>
                        <span id="price_label">{{ $product->price_min }} ~ {{ $product->price_max }}</span>
                    </div>
                </div>
            </div>

            <div class="row text-center mb-2 border-top border-bottom pt-2 pb-2">
                <div class="col-sm-3">
                    累计销量 <span class="text-danger font-weight-bold">{{ $product->sold_count }}</span>
                </div>
                <div class="col-sm-3 border-left border-right">
                    累计评价 <span class="text-danger font-weight-bold">{{ $product->review_count }}</span>
                </div>
                <div class="col-sm-3">
                    评分 {!! str_repeat('<i class="fa fa-star text-danger" aria-hidden="true"></i>', ceil($product->rating)) !!}
                </div>
            </div>

            <form class="">
                <div class="row">
                    <div class="col-md-1">
                        选择
                    </div>
                    <div class="col-md-11">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            @foreach($product->skus as $sku)
                                <label class="btn btn-sku" title="{{ $sku->description }}" data-toggle="popover">
                                    <input type="radio" name="sku" autocomplete="off" value="{{ $sku->id }}" data-id="{{ $sku->id }}"
                                           data-price="{{ $sku->price }}" data-stock="{{ $sku->stock }}"> {{ $sku->title }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="row mt-3 mb-3" style="line-height: 1.5rem">
                    <div class="col-sm-1">
                        数量
                    </div>
                    <div class="col-sm-2">
                        <input type="text" class="form-control form-control-sm" name="count" id="" aria-describedby="helpId" placeholder="" value="1">
                    </div>
                    <div class="col-sm-9 ml-0 pl-0">
                        <label for="">件, 库存 <span id="stock_label">0</span> 件</label>
                    </div>
                </div>

                <div class="row">
                    <div class="offset-sm-1 col-sm-11">
                        <button type="button" class="btn btn-success" id="btn_favor"><i class="fa fa-heart" aria-hidden="true"></i> 收藏</button>
                        <button type="button" class="btn btn-primary" id="btn_add_to_cart">加入购物车</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="card w-100 mt-3">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#product-detail-pane" data-toggle="tab">商品详情</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#product-review-pane" data-toggle="tab">用户评价</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="product-detail-pane">
                        {{-- 商品详情 --}}
                        {!! $product->description !!}
                    </div>
                    <div class="tab-pane fade" id="product-review-pane">
                        {{-- 用户评价 --}}
                        123
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section("script")
    <script>
        var skus = {!! json_encode($product->skus) !!};
        var product_id = {{ $product->id }};
        var is_favor = {!! json_encode($isFavor) !!};
        var sku_selected;

        console.debug(skus);

        function init_sku_select(radio) {
            $('#price_label').text(radio.dataset.price);
            $('#stock_label').text(radio.dataset.stock);
            sku_selected = radio.dataset.id;
            console.log(radio);
        }

        $(function () {
            $('input[name=sku]').on('change', function (event) {
                init_sku_select(this);
            });

            // 初始化第一个选项
            $('input[name=sku]:first').attr('checked', 'checked');
            init_sku_select($('input[name=sku]:first')[0]);

            $('[data-toggle="popover"]').popover({
                container: 'body',
                trigger: 'hover',
                placement: 'bottom',
            });

            // 初始化"收藏按钮"
            init_favor_btn(is_favor);
        });

        function init_favor_btn(favor = false) {
            is_favor = favor;

            console.log("init_favor_btn  ", favor);
            var favor_btn = $('#btn_favor');
            favor_btn.removeClass();
            if (!favor) {
                favor_btn.addClass("btn btn-success").html('<i class="fa fa-heart" aria-hidden="true"></i> 收藏');
            } else {
                favor_btn.addClass("btn btn-danger").html('取消收藏');
            }
        }

        $('#btn_favor').on('click', function (event) {
            // 取消收藏
            axios.request({
                url: "{{ route('products.favor', $product) }}",
                method: is_favor ? "delete" : "post"
            }).then(function (response) {
                init_favor_btn(!is_favor);
            }).catch(function (error) {
                console.log(error);
                if (error.response) {
                    if (error.response.status == 401) {
                        swal({
                            title: "未登录",
                            icon: "info"
                        }).then(function () {
                            window.location = "{{ route('login') }}";
                        });
                    } else {
                        swal({
                            title: "发生错误了 " + error.response.status,
                            text: error.response.data.message,
                            icon: "error"
                        })
                    }
                } else {
                    swal({
                        title: "发生错误了",
                        text: error.message,
                        icon: "error"
                    })
                }
            });
        });
    </script>
@stop

@section("style")
    <style>
    </style>
@stop