<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;

class SettingMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'updateOrAdd':
                $list = $request->all();
                foreach ($list as $item) {
                    if (empty(trim($item['group']))) {
                        check(false, '分组不能为空');
                    }
                    if (empty(trim($item['alias']))) {
                        check(false, '配置字段别名不能为空');
                    }
                    if (empty($item['value'])) {
                        check(false, '配置字段值不能为空');
                    }
                }
                break;
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择配置');
                }
                break;
        }
        return $next($request);
    }

}
