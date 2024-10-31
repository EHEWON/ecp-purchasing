<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\MaterialGroup;

class MaterialGroupMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->number))) {
                    check(false, '物料分类编码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '物料分类名称不能为空');
                }
                $count = MaterialGroup::where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '物料分类已存在');
                }
                $countN = MaterialGroup::where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '物料分类编码已存在');
                }
                break;
            case 'edited':
                $query = Bank::where('id', $request->id);
                $object = $query->first();
                if (empty(trim($request->number))) {
                    check(false, '分类编码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '物料分类名称不能为空');
                }
                if (empty($object)) {
                    check(false, '物料分类不存在');
                }
                $count = MaterialGroup::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '物料分类名称已存在');
                }
                $countN = MaterialGroup::whereNot('id', $request->id)
                        ->where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '物料分类编码已存在');
                }
                break;
            case 'enable':
            case 'disable':
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择物料分类');
                }
                break;
        }
        return $next($request);
    }

}
