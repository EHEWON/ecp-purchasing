<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\Division;

class DivisionMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->country_id))) {
                    check(false, '国家ID不能为空');
                }
                if (empty(trim($request->divisionlv_id))) {
                    check(false, '行政级次ID不能为空');
                }
                if (empty(trim($request->level))) {
                    check(false, '行政级次不能为空');
                }
                if (empty(trim($request->number))) {
                    check(false, '地区编码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '地区名称不能为空');
                }
                $count = Division::where('name', trim($request->name))
                        ->where('divisionlv_id', trim($request->divisionlv_id))
                        ->where('country_id', trim($request->country_id))
                        ->where('level', trim($request->level))
                        ->count();
                if (!empty($count)) {
                    check(false, '地区名称已存在');
                }
                $countN = Division::where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '地区编码已存在');
                }
                break;
            case 'edited':
                $query = Division::where('id', $request->id);
                $object = $query->first();
                if (empty($object)) {
                    check(false, '地区不存在');
                }
                if (empty(trim($request->name))) {
                    check(false, '地区名称不能为空');
                }
                if (empty(trim($request->country_id))) {
                    check(false, '国家ID不能为空');
                }
                if (empty(trim($request->divisionlv_id))) {
                    check(false, '行政级次ID不能为空');
                }
                if (empty(trim($request->level))) {
                    check(false, '行政级次不能为空');
                }
                $count = Division::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->where('divisionlv_id', trim($request->divisionlv_id))
                        ->where('country_id', trim($request->country_id))
                        ->where('level', trim($request->level))
                        ->count();
                if (!empty($count)) {
                    check(false, '地区名称已存在');
                }
                break;
            case 'enable':
            case 'disable':
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择地区');
                }
                break;
        }
        return $next($request);
    }

}
