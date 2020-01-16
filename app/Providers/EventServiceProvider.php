<?php

namespace App\Providers;

use App\Listeners\NotifyUserOrderPaid;
use App\Listeners\UpdateProductSoldCount;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // 用户注册事件
        \Illuminate\Auth\Events\Registered::class => [
            SendEmailVerificationNotification::class,   // 邮件激活
        ],

        // 订单已支付事件
        \App\Events\OrderPaid::class => [
            UpdateProductSoldCount::class,  // 更新商品销量
            NotifyUserOrderPaid::class,     // 通知用户
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
