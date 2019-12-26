<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use \Illuminate\Auth\Middleware\EnsureEmailIsVerified as BaseMiddleware;

class EnsureEmailIsVerified extends BaseMiddleware
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => "未登录"], 401);
            } else {
                return redirect()->route('login');
            }
        }

        return parent::handle($request, $next, $redirectToRoute);
    }

}
