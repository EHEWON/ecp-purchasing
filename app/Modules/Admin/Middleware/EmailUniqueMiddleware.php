<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Support\Facades\{
    Lang,
    Auth
};
use App\Common\Models\User;

/**
 * 校验邮箱唯一性
 */
class EmailUniqueMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $query = User::where('email', $request['email'])->where('deleted_flag', 'N');
        $admin = Auth::guard('admin')->user();
        $query->where('user_id', '<>', $admin->user_id);
        $exist = $query->first();
        check(empty($exist), Lang::get('user.email_exists'));
        return $next($request);
    }

}
