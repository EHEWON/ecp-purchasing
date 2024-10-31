<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\Unit;

class UnitMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->name))) {
                    check(false, '单位名称不能为空');
                }
                if (empty(trim($request->number))) {
                    check(false, '单位编码不能为空');
                }
                $count = Unit::where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '单位名称已存在');
                }
                $countN = Unit::where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '单位编码已存在');
                }
                break;
            case 'edited':
                $query = Unit::where('id', $request->id);
                $object = $query->first();
                if (empty(trim($request->name))) {
                    check(false, '单位名称不能为空');
                }
                if (empty($object)) {
                    check(false, '单位不存在');
                }
                if (empty(trim($request->name))) {
                    check(false, '单位名称不能为空');
                }
                if (empty(trim($request->number))) {
                    check(false, '单位编码不能为空');
                }
                $obj = Unit::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->count();
                if (!empty($obj)) {
                    check(false, '单位编码已存在');
                }
                $countN = Unit::whereNot('id', $request->id)
                        ->where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '单位编码已存在');
                }
                break;
            case 'enable':
                if (empty($request->ids)) {
                    check(false, '请选择单位');
                }
                break;
            case 'disable':
                if (empty($request->ids)) {
                    check(false, '请选择单位');
                }
                break;
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择单位');
                }
                break;
        }
        return $next($request);
    }

}
