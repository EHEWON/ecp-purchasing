<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\{
    User,
    UserSupplier
};
use Illuminate\Support\Facades\{
    Auth,
    Redis
};

class UserMiddleware {

    public function handle(Request $request, Closure $next) {
        $admin = Auth::guard('admin')->user();
        $token = Auth::guard('admin')->getToken();
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->realname))) {
                    check(false, '姓名不能为空');
                }
                if (empty(trim($request->email)) && empty(trim($request->phone))) {
                    check(false, '手机号或邮箱不能为都为空');
                }
                if (!empty(trim($request->email)) && !isEmail(trim($request->email))) {
                    check(false, '邮箱不正确');
                }
                if (!empty(trim($request->phone)) && !is_mobile(trim($request->phone))) {
                    check(false, '手机号不正确');
                }
                $userType = strtoupper(trim($request->user_type));
                $authUserType = $admin->user_type;

                if (empty($userType) || in_array($userType, ['PURCHASER', 'PLATFORM', 'ORG'])) {
                    if (empty($request->user_purchaser)) {
                        check(false, '请选择采购商或部门');
                        foreach ($request->user_purchaser as $org) {
                            check($org['purchaser_id'], '请选择采购商或部门');
                        }
                    }
                } elseif ($userType === 'SUPPLIER') {
                    check(empty($request->user_supplier) || $request->user_supplier['supplier_id'], '请选择供应商');
                }
                if (!empty(trim($request->email))) {
                    $countN = User::where('email', trim($request->email))
                            ->where('deleted_flag', 'N')
                            ->count();
                    check($countN === 0, '邮箱账号已存在');
                }
                if (!empty(trim($request->phone))) {
                    $countP = User::where('phone', trim($request->phone))
                            ->where('deleted_flag', 'N')
                            ->count();
                    check($countP === 0, '手机号账号已存在');
                }

                break;
            case 'edited':
                $userType = strtoupper(trim($request->user_type));
                $authUserType = $admin->user_type;

                $query = User::where('user_id', $request->id);
                $object = $query->where('deleted_flag', 'N')->first();
                if (empty($object)) {
                    check(false, '用户不存在');
                }
                if (empty(trim($request->realname))) {
                    check(false, '姓名不能为空');
                }
                if (empty(trim($request->email)) && empty(trim($request->phone))) {
                    check(false, '手机号或邮箱不能为都为空');
                }
                if (!empty(trim($request->email)) && !isEmail(trim($request->email))) {
                    check(false, '邮箱不正确');
                }
                if (!empty(trim($request->phone)) && !is_mobile(trim($request->phone))) {
                    check(false, '手机号不正确');
                }
                if (empty($userType) || in_array($userType, ['PURCHASER', 'PLATFORM', 'ORG'])) {
                    if (empty($request->user_purchaser)) {
                        check(false, '请选择采购商或部门');
                        foreach ($request->user_purchaser as $org) {
                            check($org['purchaser_id'], '请选择采购商或部门');
                        }
                    }
                } elseif ($userType === 'SUPPLIER') {
                    check(empty($request->user_supplier) || $request->user_supplier['supplier_id'], '请选择供应商');
                }
                if (!empty(trim($request->email))) {
                    $countN = User::whereNot('user_id', $request->id)
                            ->where('email', trim($request->email))
                            ->where('deleted_flag', 'N')
                            ->count();
                    check($countN === 0, '邮箱账号已存在');
                }
                if (!empty(trim($request->phone))) {
                    $countP = User::whereNot('user_id', $request->id)
                            ->where('phone', trim($request->phone))
                            ->where('deleted_flag', 'N')
                            ->count();
                    check($countP === 0, '手机号账号已存在');
                }
                break;
            case 'enable':
                check(!empty($request->ids), '请选择用户');
                break;
            case 'disable':
            case 'delete':
                $authUserType = $admin->user_type;
                check(!empty($request->ids), '请选择用户');
                if ($authUserType === 'SUPPLIER' && Redis::command('exists', ['cur_pid' . $token])) {
                    $curId = Redis::get('cur_pid' . $token);
                    $count = UserSupplier::whereIn('user_id', $request->ids)
                            ->whereNot('supplier_id', $curId)
                            ->where('deleted_flag', 'N')
                            ->count();
                    check($count === 0, '您不能禁用或删除其他供应商的用户');
                    check(!in_array($admin->user_id, $request->ids), '不能禁用或删除当前用户');
                    $countN = UserSupplier::whereIn('user_id', $request->ids)
                            ->where('is_manager', 1)
                            ->where('deleted_flag', 'N')
                            ->count();
                    check($countN === 0, '不能禁用或删除供应商管理账号');
                    return $next($request);
                }

                $countN = UserSupplier::whereIn('user_id', $request->ids)
                        ->where('is_manager', 1)
                        ->where('deleted_flag', 'N')
                        ->groupBy('supplier_id')
                        ->count();
                check($countN === 0, '不能禁用或删除供应商管理账号');
                break;
        }
        return $next($request);
    }

}
