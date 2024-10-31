<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Support\Facades\{
    Lang,
    Redis
};

/**
 * 校验邮箱验证码
 */
class EmailVerifyMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $code = Redis::get($request['email_code_key']);
        $emailCode = trim($request['email_code']) ?? '';
        check(!empty($emailCode) && strtolower($code) === strtolower(trim($emailCode)), Lang::get('user.email_code_verify'));
        return $next($request);
    }

}
