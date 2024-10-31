<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use App\Common\Models\User;
use Illuminate\Support\Facades\{
    Auth,
    Lang
};

/**
 * 校验手机号
 */
class NewPhoneUniqueMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $phone = $request['phone'] ?? '';
        $query = User::where('phone', $phone)->where('deleted_flag', 'N');
        $admin = Auth::guard('admin')->user();
        $query->where('user_id', '<>', $admin->user_id);
        $exist = $query->first();
        check(empty($exist), Lang::get('user.phone_exists'));
        return $next($request);
    }

}
