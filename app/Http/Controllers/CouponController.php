<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function show($code, CouponService $service, Request $request)
    {
        /* @var Coupon $coupon */
        if (!$coupon = Coupon::query()->where('code', $code)->first()) {
            abort(404);
        }

        $service->checkValid($coupon, null, request()->user());

        return $coupon;
    }
}
