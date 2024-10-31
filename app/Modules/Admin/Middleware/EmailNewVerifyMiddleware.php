<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Support\Facades\{
    Lang,
    Redis,
    Auth
};
use App\Common\Models\User;

/**
 * 校验邮箱验证码
 */
class EmailNewVerifyMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $code = Redis::get($request['email_new_code_key']);
        $email = $request['new_email'];
        $emailCode = trim($request['email_new_code']) ?? '';
        check(!empty($emailCode) && strtolower($code) === strtolower(trim($emailCode)), Lang::get('user.email_code_verify'));
        $admin = Auth::guard('admin')->user();
        check($admin->email !== $email, Lang::get('user.email_account_not_changed'));
        $count = User::where('email', $email)->where('deleted_flag', 'N')->count();
        check($count === 0, Lang::get('user.email_account_already_exists'));
        return $next($request);
    }

}
