<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\{
    Purchaser,
    User,
    PurchaserBusiness
};

class PurchaserMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->social_code))) {
                    check(false, '统一社会信用代码不能为空');
                }
                if (empty(trim($request->number))) {
                    check(false, '编码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '名称不能为空');
                }
                $count = Purchaser::where('name', trim($request->name))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($count)) {
                    check(false, '名称已存在');
                }
                $countN = Purchaser::where('number', trim($request->number))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($countN)) {
                    check(false, '编码已存在');
                }
                $countS = PurchaserBusiness::where('social_code', trim($request->social_code))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($countS)) {
                    check(false, '统一社会信用代码已存在');
                }
                break;
            case 'edited':
                $query = Purchaser::where('id', $request->id);
                $object = $query->where('deleted_flag', 'N')->first();
                if (empty($object)) {
                    check(false, '采购商不存在');
                }
                if (empty(trim($request->social_code))) {
                    check(false, '统一社会信用代码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '名称不能为空');
                }
                $count = Purchaser::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($count)) {
                    check(false, '名称已存在');
                }
                $countS = PurchaserBusiness::whereNot('purchaser_id', $request->id)
                        ->where('social_code', trim($request->social_code))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($countS)) {
                    check(false, '统一社会信用代码已存在');
                }
                break;
            case 'enable':
                if (empty($request->ids)) {
                    check(false, '请选择采购商');
                }
                break;
            case 'disable':
                if (empty($request->ids)) {
                    check(false, '请选择采购商');
                }
                $countC = Purchaser::whereIn('parent_id', $request->ids)
                        ->where('enable', 1)
                        ->where('deleted_flag', 'N')
                        ->count();
                check($countC === 0, '已关联子公司或组织的采购商不能禁用');
                $userTable = (new \App\Common\Models\User)->getTable();
                $userPurchaserTable = (new \App\Common\Models\UserPurchaser)->getTable();
                $count = User::from($userTable . ' as u')
                        ->join($userPurchaserTable . ' as up', function($join) {
                            $join->on('up.user_id', '=', 'u.user_id');
                        })
                        ->where('u.enable', 1)
                        ->where('u.deleted_flag', 'N')
                        ->whereIn('up.purchaser_id', $request->ids)
                        ->count('u.user_id');
                check($count === 0, '已关联用户的采购商不能禁用');

                break;
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择采购商');
                }
                $countC = Purchaser::whereIn('parent_id', $request->ids)
                        ->where('enable', 1)
                        ->where('deleted_flag', 'N')
                        ->count();
                check($countC === 0, '已关联子公司或组织的采购商不能删除');
                $userTable = (new \App\Common\Models\User)->getTable();
                $userPurchaserTable = (new \App\Common\Models\UserPurchaser)->getTable();
                $count = User::from($userTable . ' as u')
                        ->join($userPurchaserTable . ' as up', function($join) {
                            $join->on('up.user_id', '=', 'u.id');
                        })
                        ->where('u.enable', 1)
                        ->where('u.deleted_flag', 'N')
                        ->whereIn('up.purchaser_id', $request->ids)
                        ->count('u.user_id');
                check($count === 0, '已关联用户的采购商不能删除');
                break;
        }
        return $next($request);
    }

}
