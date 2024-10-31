<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\SupplierGrade;

class SupplierGradeMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->number))) {
                    check(false, '分级方案编码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '分级方案名称不能为空');
                }
                $count = SupplierGrade::where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '分级方案已存在');
                }
                $countN = SupplierGrade::where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '分级方案编码已存在');
                }
                break;
            case 'edited':
                $query = SupplierGrade::where('id', $request->id);
                $object = $query->first();
                if (empty(trim($request->name))) {
                    check(false, '分级方案名称不能为空');
                }
                if (empty($object)) {
                    check(false, '分级方案不存在');
                }
                $count = SupplierGrade::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '分级方案名称已存在');
                }
                break;
            case 'enable':
            case 'disable':
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择分级方案');
                }
                break;
        }
        return $next($request);
    }

}
