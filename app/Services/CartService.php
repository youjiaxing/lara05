<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2019/12/31 18:32
 */

namespace App\Services;

use App\Models\Cart;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * 从购物车移除商品
     *
     * @param User                                           $user
     * @param Collection<ProductSku>|ProductSku[]|ProductSku $productSkus
     *
     * @return int
     */
    public function removeProductSkus(User $user, $productSkus)
    {
        return $user->carts()
            ->whereIn('product_sku_id', collect($productSkus)->pluck('id'))
            ->delete();
    }

    /**
     * 往购物车添加东西
     *
     * @param User $user
     * @param int $productSkuId
     * @param int $quantity
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function add($user, $productSkuId, $quantity = 1)
    {
        $cart = Cart::query()->firstOrNew([
            'user_id' => $user->id,
            'product_sku_id' => $productSkuId,
        ]);
        $cart->amount = $quantity;
        $cart->save();
        return $cart;
    }
}
