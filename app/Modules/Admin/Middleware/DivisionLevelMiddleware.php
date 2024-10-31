<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\{
    Division,
    DivisionLevel
};

class DivisionLevelMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->country_id))) {
                    check(false, '国家ID不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '行政级次不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '国家名称不能为空');
                }
                if (empty(trim($request->number))) {
                    check(false, '行政级次编码不能为空');
                }
                $count = DivisionLevel::where('name', trim($request->name))
                        ->where('country_id', intval($request->country_id))
                        ->count();
                if (!empty($count)) {
                    check(false, '行政级次名称已存在');
                }
                $countN = DivisionLevel::where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '地区编码已存在');
                }
                break;
            case 'edited':
                $query = DivisionLevel::where('id', $request->id);
                $object = $query->first();
                if (empty($object)) {
                    check(false, '行政级次不存在');
                }
                if (empty(trim($request->country_id))) {
                    check(false, '国家ID不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '行政级次名称不能为空');
                }
                $count = DivisionLevel::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->where('country_id', intval($request->country_id))
                        ->count();
                if (!empty($count)) {
                    check(false, '行政级次名称已存在');
                }
                break;
            case 'enable':
                if (empty($request->ids)) {
                    check(false, '请选择行政级次');
                }
                break;
            case 'disable':
                check(!empty($request->ids), '请选择行政级次');
                $count = Division::whereIn('divisionlv_id', $request->ids)
                        ->count();
                check($count === 0, '已关联地区的行政级次不能禁用');
                break;
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择行政级次');
                }
                $count = Division::whereIn('divisionlv_id', $request->ids)
                        ->count();
                check($count === 0, '已关联地区的行政级次不能删除');
                break;
        }
        return $next($request);
    }

}
