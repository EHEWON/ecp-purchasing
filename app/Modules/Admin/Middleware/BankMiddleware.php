<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\Bank;

class BankMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty(trim($request->name))) {
                    check(false, '行名行号名称不能为空');
                }
                if (empty(trim($request->number))) {
                    check(false, '行名行号编码不能为空');
                }
                $count = Bank::where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '行名行号已存在');
                }
                $countN = Bank::where('number', trim($request->number))
                        ->count();
                if (!empty($countN)) {
                    check(false, '行名行号编码已存在');
                }
                break;
            case 'edited':
                $query = Bank::where('id', $request->id);
                $object = $query->first();
                if (empty(trim($request->name))) {
                    check(false, '行名行号名称不能为空');
                }
                if (empty($object)) {
                    check(false, '行名行号不存在');
                }
                $count = Bank::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '行名行号名称已存在');
                }

                break;
            case 'enable':
            case 'disable':
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择银行');
                }
                break;
        }
        return $next($request);
    }

}
