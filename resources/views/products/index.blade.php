@extends("layouts.app")
@section("title", "商品列表")

@section("content")
    <div class="card">
        <div class="card-header">
            <form action="{{ route('products.index') }}" method="get" class="form-inline" id="query_form">
                <div class="col-12">
                    <div class="float-left">
                        <input type="text" class="form-control" name="search" id="" aria-describedby="helpId" placeholder="" value="">
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>

                    <div class="float-right">
                        <select class="form-control" name="sort" id="sort_select">
                            <option style="">排序方式</option>
                            <option value="id:desc" selected>最新排序</option>
                            <option value="price_min:asc">价格升序</option>
                            <option value="price_max:desc">价格降序</option>
                            <option value="sold_count:asc">销量升序</option>
                            <option value="sold_count:desc">销量降序</option>
                            <option value="rating:asc">评分升序</option>
                            <option value="rating:desc">评分降序</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="products-list row row-cols-1 row-cols-md-4">
                @foreach($products as $product)
                    <div class="col mb-4">
                        <div class="card products-list-item">
                            <a href="{{ route('products.show', [$product->id]) }}"><img class="card-img-top" src="{{ $product->imageUrl }}" alt="Card image cap"></a>
                            <div class="card-body">
                                <div class="product-price text-danger">
                                    <i class="fa fa-rmb" aria-hidden="true"></i>
                                    {{ $product->price_min }} ~ {{ $product->price_max }}
                                </div>

                                <a href="{{ route('products.show', [$product->id]) }}">
                                    <p class="product-title mb-0">{{ $product->title }}</p>
                                </a>
                            </div>
                            <div class="card-footer clearfix">
                                <div class="float-left">
                                    销量: <span class="sold_count">{{ $product->sold_count }}</span>
                                </div>
                                <div class="float-right">
                                    评价: <span class="review_count">{{ $product->review_count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 清除浮动 --}}
            <div class="clearfix">
                <div class="float-right">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section("script")
    <script>
        $(function () {
            var filters = {!! json_encode($filters) !!};
            console.log(filters);
            var init_sort = filters.sort;

            $('#query_form input[name=search]').val(filters.search);

            var sort_select = $('#sort_select');
            // console.log("init sort: " + init_sort);

            // if (!!init_sort) {
                sort_select.val(init_sort);
            // }

            sort_select.on('change', function (event) {
                $('#query_form').submit();
            });
        });

    </script>
@stop

<style>
</style>
