<?php
/**
 * @var \App\Models\Order $order
 */
?>

@extends("layouts.app")
@section("title", "订单商品评价")

@section("content")
    <div class="card">
        <div class="card-header">
            订单商品评价
        </div>
        <form method="post" action="{{ route('orders.review', [$order->id]) }}">
            {{ csrf_field() }}
            <div class="card-body">
                @if ($errors->has('reviews'))
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->get('reviews') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>商品</th>
                        <th>打分</th>
                        <th>评价</th>
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
                            <td>
                                <div class="media">
                                    <a class="d-flex" href="{{ route('products.show', [$product->id]) }}">
                                        <img src="{{ $product->image_url }}" alt="" width="75px" height="75px" class="mr-3">
                                    </a>
                                    <div class="media-body">
                                        <h5>{{ $product->title }}</h5>
                                        <div class="text-secondary">{{ $productSku->title }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if ($order->isReviewed())
                                    <ul class="rate-area-show">
                                        {!! str_repeat('<i class="fa fa-star" aria-hidden="true"></i>', $orderItem->review_rating) !!}
                                        {!! str_repeat('<i class="fa fa-star-o" aria-hidden="true"></i>', 5 - $orderItem->review_rating) !!}
                                    </ul>
                                @else
                                    <ul class="rate-area-form">
                                        @for($i=5; $i>=1; $i--)
                                            <input type="radio" id="star-{{ $orderItem->id }}-{{ $i }}" name="reviews[{{ $orderItem->id }}][rating]"
                                                   value="{{ $i }}" hidden @if(old("reviews.{$orderItem->id}.rating", 5) == $i) checked @endif >
                                            <label for="star-{{ $orderItem->id }}-{{ $i }}"></label>
                                        @endfor
                                    </ul>
                                @endif
                            </td>
                            {{--@include('common._validation_errors')--}}
                            <td>
                                @if ($order->isReviewed())
                                    <p>{{ $orderItem->review_content }}</p>
                                @else
                                    @php($contentKey = "reviews.{$orderItem->id}.content")
                                    {{--                                    <div class="@if($errors->has("reviews.{$orderItem->id}.content")) has-error @endif">--}}
                                    <textarea class="form-control @if($errors->has($contentKey)) is-invalid @endif"
                                              name="reviews[{{ $orderItem->id }}][content]" rows="3">
                                        {{ old("reviews.{$orderItem->id}.content") }}
                                    </textarea>
                                    @if($errors->has($contentKey))
                                        @foreach($errors->get($contentKey) as $error)
                                            {{--                                            <span class="help-block">1 {{ $error }}</span>--}}
                                            <span class="invalid-feedback" role="alert"><strong>{{ $error }}</strong></span>
                                        @endforeach
                                    @endif
                                    {{--                                    </div>--}}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                @if (!$order->isReviewed())
                    <button type="submit" class="btn btn-primary">提交</button>
                @else
                    <a id="" class="btn btn-primary" href="{{ route('orders.show', [$order->id]) }}" role="button">查看订单</a>
                @endif
            </div>
        </form>
    </div>
@stop

@section("script")

@stop

@section("style")
    <style>

    </style>
@stop
