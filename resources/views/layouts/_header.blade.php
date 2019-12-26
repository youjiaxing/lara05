<nav class="navbar navbar-expand-lg navbar-light bg-light navbar-static-top">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name') }}</a>


        <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#collapsibleNavId" aria-controls="collapsibleNavId"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="collapsibleNavId">
            <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
                {{--            <li class="nav-item active">--}}
                {{--                <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>--}}
                {{--            </li>--}}
                {{--            <li class="nav-item">--}}
                {{--                <a class="nav-link" href="#">Link</a>--}}
                {{--            </li>--}}
                {{--            <li class="nav-item dropdown">--}}
                {{--                <a class="nav-link dropdown-toggle" href="#" id="dropdownId" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Dropdown</a>--}}
                {{--                <div class="dropdown-menu" aria-labelledby="dropdownId">--}}
                {{--                    <a class="dropdown-item" href="#">Action 1</a>--}}
                {{--                    <a class="dropdown-item" href="#">Action 2</a>--}}
                {{--                </div>--}}
                {{--            </li>--}}
            </ul>


            <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
                @guest
                    <li class="nav-item"><a href="{{ route('login') }}" class="nav-link">{{ __('Login') }}</a></li>
                    <li class="nav-item"><a href="{{ route("register") }}" class="nav-link">@lang("Sign Up")</a></li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true"
                           aria-expanded="false">
                            <img src="https://cdn.learnku.com/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/60/h/60" class="img-fluid rounded-top"
                                 alt="" width="30" height="30"> {{ \Illuminate\Support\Facades\Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ route('user_addresses.index') }}" class="dropdown-item">收货地址</a>
                            <a href="{{ route('products.favorites') }}" class="dropdown-item">收藏列表</a>

                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item" id="logout_btn" onclick="event.preventDefault(); document.getElementById('logout_form').submit();">退出登录</a>
                            <form action="{{ route('logout') }}" method="post" id="logout_form">
{{--                                {{ csrf_field() }}--}}
                            </form>
{{--                            <a class="dropdown-item" href="#">Action</a>--}}
{{--                            <a class="dropdown-item" href="#">Another action</a>--}}
{{--                            <div class="dropdown-divider"></div>--}}
{{--                            <a class="dropdown-item" href="#">Action</a>--}}
                        </div>
                    </li>

                @endguest
            </ul>
        </div>
    </div>
</nav>

{{--<script>--}}
{{--    var logout_btn = document.getElementById("logout_btn");--}}
{{--    logout_btn.onclick = function (event) {--}}
{{--        event.preventDefault();--}}
{{--        document.getElementById("logout_form").submit();--}}
{{--    }--}}

{{--</script>--}}
