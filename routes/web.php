<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);
//Route::get('/', 'PagesController@root')->name('root');
Route::redirect('/', '/products')->name('root');
// 商品
Route::get("/products", "ProductController@index")->name('products.index');
Route::get("/products/{product}", "ProductController@show")->name('products.show')->where('product', '[0-9]+');

// 需登陆并经过邮件认证
Route::middleware(['verified'])->group(function () {
    // 收货地址
    Route::resource('user_addresses', 'UserAddressController');

    // 商品收藏
    Route::post('/products/{product}/favor', 'ProductController@favor')->name('products.favor');
    // 商品取消收藏
    Route::delete('/products/{product}/favor', 'ProductController@disfavor')->name('products.disfavor');
    // 商品收藏列表
    Route::get("/products/favorites", "ProductController@favorites")->name('products.favorites');

    // 购物车
    Route::get("/carts", 'CartController@index')->name('cart.index');
    Route::delete("/carts/{cart}", 'CartController@destroy')->name('cart.destroy');
    Route::post("/carts/{product_sku}", 'CartController@add')->name('cart.add');

    // 订单
    Route::post('/orders', 'OrderController@store')->name('orders.store');
    Route::get('/orders', 'OrderController@index')->name('orders.index');
    Route::get('/orders/{order}', 'OrderController@show')->name('orders.show');
    Route::post('/orders/{order}/receive', 'OrderController@receive')->name('orders.receive');
    //订单-评价
    Route::get('/orders/{order}/review', 'OrderController@reviewShow')->name('orders.review');
    Route::post('/orders/{order}/review', 'OrderController@reviewStore')->name('orders.review');

    // 订单-支付
    // 支付宝前端回调
    Route::get('/payments/alipay/return', 'PaymentController@alipayReturn')->name('payments.alipay.return');
    Route::get('/payments/{order}/alipay', 'PaymentController@alipay')->name('payments.alipay');

    // 退款
    Route::get('/refunds/{order}/create', 'OrderRefundController@create')->name('orderRefunds.create');
    // 部分退款
    Route::post('/refunds/{order}/part', 'OrderRefundController@refundPart')->name('orderRefunds.refundPart');
    // 全部退款
    Route::post('/refunds/{order}/all', 'OrderRefundController@refundAll')->name('orderRefunds.refundAll');
    Route::get('/refunds/{order}/{order_refund}', 'OrderRefundController@show')->name('orderRefunds.show');

    // 兑换券
    Route::get('/coupons/{code}', 'CouponController@show')->name('coupons.show');
});

// 支付宝服务端回调
Route::post('/payments/alipay/notify', 'PaymentController@alipayNotify')->name('payments.alipay.notify');
