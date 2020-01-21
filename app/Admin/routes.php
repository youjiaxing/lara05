<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('users', 'UsersController')->names('admin.users');
    $router->resource('products', 'ProductsController')->names('admin.products');

    $router->post('orders/{order}/express', 'OrdersController@express')->name('admin.orders.express');
    $router->resource('orders', 'OrdersController')->names('admin.orders');
    $router->post('order-refund/{orderRefund}/reject', 'OrderRefundController@reject')->name('admin.orderRefund.reject');
    $router->post('order-refund/{orderRefund}/accept', 'OrderRefundController@accept')->name('admin.orderRefund.accept');
});
