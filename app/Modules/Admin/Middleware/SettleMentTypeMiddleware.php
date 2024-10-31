<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\SettleMentType;

class SettleMentTypeMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':

                if (empty(trim($request->name))) {
                    check(false, '名称不能为空');
                }
                $count = SettleMentType::where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '名称已存在');
                }
                break;
            case 'edited':
                $query = SettleMentType::where('id', $request->id);
                $object = $query->first();

                if (empty(trim($request->name))) {
                    check(false, '名称不能为空');
                }
                if (empty($object)) {
                    check(false, '单位不存在');
                }
                $count = SettleMentType::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '名称已存在');
                }
                break;
            case 'enable':
            case 'disable':
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择结算方式');
                }
                break;
        }
        return $next($request);
    }

}
