@extends("layouts.app")
@section("title", "支付结果")

@section("content")
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-success" role="alert">
                        @if ($success)
                            <strong>订单支付成功, 等待 <span id="delay_second">{{ $delay }}</span> 秒后自动跳转</strong>
                        @else
                            <strong>{{ $msg }}</strong>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section("script")
    <script>
            @if ($success)
        var delay = {{ $delay }};
        var delay_second_span = $('#delay_second');
        var count_down_id = setInterval(function () {
            delay -= 1;
            console.log("delay: " + delay);

            if (delay <= 0) {
                clearInterval(this);
                redirect_to_order();
            } else {
                delay_second_span.text(delay);
            }
        }, 1000);

        function redirect_to_order() {
            window.location = "{{ route('orders.show', [$order->id]) }}";
        }
        @endif
    </script>
@stop
