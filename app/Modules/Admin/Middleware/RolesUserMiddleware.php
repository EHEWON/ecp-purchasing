<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;

class RolesUserMiddleware {

    public function handle(Request $request, Closure $next) {
        $list = $request->all();
        if (empty($list['user_id'])) {
            check(false, '请选择用户');
        }
        foreach ($list['items'] as $item) {
            if (empty($item['role_ids'])) {
                continue;
            }
            if (empty($item['team_id']) || empty(trim($item['team_id']))) {
                check(false, '请选择采购商或组织机构');
            }
            if (empty(trim($item['role_group']))) {
                check(false, '请选择权限类型');
            }
        }
        return $next($request);
    }

}
