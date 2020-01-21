<?php

namespace App\Providers;

use App\Extensions\Cache\FileStore;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Gateways\Alipay;
use Yansongda\Pay\Gateways\Wechat;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 支付宝支付
        $this->app->singleton(Alipay::class, function (Application $app) {
            $config = config('pay.alipay');
            $config['return_url'] = route('payments.alipay.return');

            if ($app->environment('local')) {
                $config['notify_url'] = config('pay.alipay.notify_url_local'); // 内网临时用的第三方调试地址
            } else {
                $config['notify_url'] = route('payments.alipay.notify');
            }

            if ($app->environment() === 'production') {
                $config['log']['level'] = 'info';
            } else {
                $config['log']['level'] = 'debug';
            }
            return Pay::alipay($config);
        });
        $this->app->alias(Alipay::class, 'alipay');

        // 微信支付
        $this->app->singleton(Wechat::class, function (Application $app) {
            $config = config('pay.wechat');
//            $config['notify_url'] = route('payments.wechat.notify');
            if ($app->environment() === 'production') {
                $config['log']['level'] = 'info';
            } else {
                $config['log']['level'] = 'debug';
            }
            return Pay::wechat($config);
        });
        $this->app->alias(Wechat::class, 'wechat');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // fix "file" driver bug
        Cache::extend('file2', function ($app, $config) {
            return Cache::repository(new FileStore($app['files'], $config['path']));
        });
    }
}
