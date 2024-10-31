<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Support\Facades\{
    Lang,
    Redis
};

/**
 * 校验手机号验证码
 */
class AccountVerifyMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $code = Redis::get($request['account_code_key']);
        $accontCode = trim($request['account_code']) ?? '';
        check(!empty($accontCode) && strtolower($code) === strtolower(trim($accontCode)), Lang::get('user.acount_verify'));
        return $next($request);
    }

}
