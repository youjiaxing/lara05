<?php

namespace App\Exceptions;

use Exception;

class InvalidCouponException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->message], $this->code);
        } else {
            return back()->withInput()->withErrors(['coupon' => $this->message]);
        }
    }
}
