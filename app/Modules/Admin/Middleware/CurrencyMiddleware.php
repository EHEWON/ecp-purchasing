<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\Currency;

class CurrencyMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->number))) {
                    check(false, '币种不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '币种名称不能为空');
                }
                $count = Currency::where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '币种已存在');
                }
                $countN = Currency::where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '币种编码已存在');
                }
                break;
            case 'edited':
                $query = Currency::where('id', $request->id);
                $object = $query->first();
                if (empty(trim($request->number))) {
                    check(false, '币种编码不能为空');
                }
                if (empty(trim($request->name))) {
                    check(false, '币种名称不能为空');
                }
                if (empty($object)) {
                    check(false, '币种不存在');
                }
                $count = Currency::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '币种名称已存在');
                }
                $countN = Currency::whereNot('id', $request->id)
                        ->where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '币种编码已存在');
                }
                break;
            case 'enable':
            case 'disable':
                if (empty($request->ids)) {
                    check(false, '请选择币种');
                }
                break;
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择币种');
                }
                $countN = Currency::whereIn('id', $request->ids)
                        ->where('is_system', 1)
                        ->count();
                if (!empty($countN)) {
                    check(false, '系统预置币种不能删除');
                }
                break;
        }
        return $next($request);
    }

}
