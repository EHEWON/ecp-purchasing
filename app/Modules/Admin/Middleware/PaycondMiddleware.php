<?php

/**




 */

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\Paycond;

class PaycondMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->number))) {
                    check(false, '付款条件编码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '付款条件名称不能为空');
                }
                $count = Paycond::where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '付款条件已存在');
                }
                $countN = Paycond::where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '付款条件编码已存在');
                }
                break;
            case 'edited':
                $query = Paycond::where('id', $request->id);
                $object = $query->first();
                if (empty(trim($request->number))) {
                    check(false, '分类编码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '付款条件名称不能为空');
                }
                if (empty($object)) {
                    check(false, '付款条件不存在');
                }
                $count = Paycond::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '付款条件名称已存在');
                }
                $countN = Paycond::whereNot('id', $request->id)
                        ->where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '付款条件编码已存在');
                }
                break;
            case 'enable':
            case 'disable':
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择付款条件');
                }
                break;
        }
        return $next($request);
    }

}
