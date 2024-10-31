<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;

class SupplierGradentryMiddleware {

    public function handle(Request $request, Closure $next) {
        if (empty($request['gradentry'])) {
            check(false, '分级规则不能为空');
        }
        foreach ($request['gradentry'] as $gradentry) {
            check(!empty($gradentry['eva_grade_id']), '评估等级不能为空');
            check(!empty($gradentry['score_from']) && $gradentry['score_from'] < $gradentry['score_to'], '评估得分不正确');
        }
        return $next($request);
    }

}
