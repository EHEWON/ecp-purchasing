<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\{
    SupplierEvaGrade,
    SupplierGrade,
    SupplierGradentry
};

class SupplierEvaGradeMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->number))) {
                    check(false, '评估等级编码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '评估等级名称不能为空');
                }
                $count = SupplierEvaGrade::where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '评估等级已存在');
                }
                $countN = SupplierEvaGrade::where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '评估等级编码已存在');
                }
                break;
            case 'edited':
                $query = SupplierEvaGrade::where('id', $request->id);
                $object = $query->first();
                if (empty(trim($request->number))) {
                    check(false, '分类编码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '评估等级名称不能为空');
                }
                if (empty($object)) {
                    check(false, '评估等级不存在');
                }
                $count = SupplierEvaGrade::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '评估等级名称已存在');
                }
                $countN = SupplierEvaGrade::whereNot('id', $request->id)
                        ->where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '评估等级编码已存在');
                }
                break;
            case 'enable':
                if (empty($request->ids)) {
                    check(false, '请选择评估等级');
                }
                break;
            case 'disable':
                if (empty($request->ids)) {
                    check(false, '请选择评估等级');
                }
                $gradeTable = (new SupplierGrade)->getTable();
                $gradeentryTable = (new SupplierGradentry)->getTable();
                $count = SupplierGrade::from($gradeTable . ' as g')
                        ->join($gradeentryTable . ' as ge', function($join) {
                            $join->on('ge.grade_id', '=', 'g.id');
                        })
                        ->whereIn('ge.eva_grade_id', $request->ids)
                        ->count();
                check($count === 0, '已关联分级方案的评估等级不能禁用');
                break;
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择评估等级');
                }
                $gradeTable = (new SupplierGrade)->getTable();
                $gradeentryTable = (new SupplierGradentry)->getTable();
                $count = SupplierGrade::from($gradeTable . ' as g')
                        ->join($gradeentryTable . ' as ge', function($join) {
                            $join->on('ge.grade_id', '=', 'g.id');
                        })
                        ->whereIn('ge.eva_grade_id', $request->ids)
                        ->count();
                check($count === 0, '已关联分级方案的评估等级不能删除');
                break;
        }
        return $next($request);
    }

}
