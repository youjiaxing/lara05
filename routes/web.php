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
});
