<?php

namespace App\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use Laravel\Lumen\Http\Request;
use App\Modules\Admin\Admin;
use Illuminate\Support\Facades\{
    Lang,
    Log
};

class Authenticate {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        $token = !empty($request->header('token')) ? $request->header('token') : (!empty($request->cookie('token')) ? $request->cookie('token') : $request->post('token'));

        Log::info("login token:", [$token]);

        check(!empty($token), Lang::get('customer.login_expired'), 401);

        $user = Redis::hgetall('token_' . $token);

        Log::info("login user:", $user);

        check(!empty($user), Lang::get('customer.login_expired'), 401);
        Redis::expire('token_' . $token, config('login.token_expired'));
        Admin::setUser($user);
        Admin::setUid(!empty($user['user_id']) ? $user['user_id'] : $user['id']);
        return $next($request);
    }

}
