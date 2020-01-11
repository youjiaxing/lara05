<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Cart;
use App\Models\ProductSku;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CartController extends Controller
{
    public function index()
    {
//        $carts = user()->carts->load('productSku.product');
        $carts = user()->carts()->with('productSku.product')->get();
        return view('carts.index', ['carts' => $carts]);
    }

    /**
     * @param ProductSku $productSku
     * @param Request    $request
     *
     * @throws InvalidRequestException
     */
    public function add(ProductSku $productSku, Request $request, CartService $cartService)
    {
        $validatedData = $request->validate([
            'quantity' => 'required|numeric|min:1'
        ]);
        $quantity = $validatedData['quantity'] ?? 1;

        if (!$productSku->product->is_sale) {
            throw new InvalidRequestException("商品已下架");
        }

        if ($quantity > $productSku->stock) {
            throw new InvalidRequestException("库存不足");
        }

        $cart = $cartService->add(Auth::user(), $productSku->id, $quantity);
    }

    public function destroy(Cart $cart)
    {
        if ($cart->user->id != user()->id) {
            throw new AccessDeniedHttpException();
        }
        $cart->delete();
    }
}
