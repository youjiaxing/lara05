<?php
function route_class()
{
    return str_replace('.', '-', \Illuminate\Support\Facades\Route::currentRouteName());
}

/**
 * @return \App\Models\User|null
 */
function user()
{
    return \Illuminate\Support\Facades\Auth::user();
}

/**
 * @return Yansongda\Pay\Gateways\Alipay
 */
function alipay()
{
    return app('alipay');
}

function wechat()
{
    return app('wechat');
}
