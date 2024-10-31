<?php

/**




 */

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\{
    Purchaser,
    User
};

class OrgMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->name))) {
                    check(false, '名称不能为空');
                }
                if (empty(trim($request->number))) {
                    check(false, '编码不能为空');
                }
                if (empty(trim($request->parent_id))) {
                    check(false, '请选择父供应商或父组织');
                }
                $count = Purchaser::where('name', trim($request->name))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($count)) {
                    check(false, '组织机构已存在');
                }
                $countN = Purchaser::where('number', trim($request->number))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($countN)) {
                    check(false, '编码已存在');
                }
                break;
            case 'edited':
                $query = Purchaser::where('id', $request->id);
                $object = $query->where('deleted_flag', 'N')->first();
                if (empty(trim($request->name))) {
                    check(false, '组织机构名称不能为空');
                }
                if (empty(trim($request->parent_id))) {
                    check(false, '请选择父供应商或父组织');
                }
                if (empty($object)) {
                    check(false, '组织机构不存在');
                }
                $count = Purchaser::whereNot('id', $request->id)
                        ->where('purchaser_type', 'ORG')
                        ->where('name', trim($request->name))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($count)) {
                    check(false, '组织机构名称已存在');
                }
                break;
            case 'enable':
                if (empty($request->ids)) {
                    check(false, '请选择组织机构');
                }
                break;
            case 'disable':
                if (empty($request->ids)) {
                    check(false, '请选择组织机构');
                }

                $userTable = (new User)->getTable();
                $userPurchaserTable = (new \App\Common\Models\UserPurchaser)->getTable();
                $count = User::from($userTable . ' as u')
                        ->join($userPurchaserTable . ' as up', function($join) {
                            $join->on('up.user_id', '=', 'u.user_id');
                        })
                        ->whereIn('up.purchaser_id', $request->ids)
                        ->where('u.enable', 1)
                        ->where('u.deleted_flag', 'N')
                        ->count('u.user_id');
                check($count === 0, '已关联用户的的组织机构不能禁用');
                break;
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择组织机构');
                }
                $userTable = (new User)->getTable();
                $userPurchaserTable = (new \App\Common\Models\UserPurchaser)->getTable();
                $count = User::from($userTable . ' as u')
                        ->join($userPurchaserTable . ' as up', function($join) {
                            $join->on('up.user_id', '=', 'u.user_id');
                        })
                        ->whereIn('up.purchaser_id', $request->ids)
                        ->where('u.enable', 1)
                        ->where('u.deleted_flag', 'N')
                        ->count('u.user_id');
                check($count === 0, '已关联用户的的组织机构不能删除');
                break;
        }
        return $next($request);
    }

}
