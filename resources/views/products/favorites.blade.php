@extends("layouts.app")
@section("title", "我的收藏")

@section("content")
    <div class="row row-cols-1 row-cols-sm-3 row-cols-md-5">
        @foreach($products as $product)
            <div class="col">
                <div class="card h-100">
                    <img src="{{ $product->imageUrl }}" alt="" class="card-img-top">
                    <div class="card-body">
                        <div class="price font-weight-bold text-danger"><i class="fa fa-rmb" aria-hidden="true"></i> {{ $product->price_min }} ~ {{ $product->price_max }}</div>
                        <div class="">{{ $product->title }}</div>
{{--                        <h3 class="card-text">{{ $product->title }}</h3>--}}
                        {{--                        <p class="card-text">Text</p>--}}
                    </div>
                    <div class="card-footer bg-white">
                        <div class="clearfix">
                            <div class="float-left">
                                销量: <span class="font-weight-bold text-warning">{{ $product->sold_count }}</span>
                            </div>
                            <div class="float-right">
                                评价: <span class="font-weight-bold text-info">{{ $product->review_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-4">
        {!! $products->links() !!}
    </div>
@stop

@section("style")
    <style>
        .pagination {
            width: 100%;
            justify-content: flex-end;
        }
    </style>
    @stop
