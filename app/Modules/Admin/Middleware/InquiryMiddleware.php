<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;

class InquiryMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                check(!empty(trim($base['title'])), '请输入询价标题');
                check(!empty(trim($base['end_date'])), '请选择报价截止日期');
                check(!empty(trim($base['sup_scope'])), '请选择询价范围');
                check(!empty(trim($base['open_type'])), '请选择开标方式');
                check(!empty(trim($base['person_id'])), '请选择采购员');
                break;
            case 'edited':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                check(!empty(trim($base['title'])), '请输入询价标题');
                check(!empty(trim($base['end_date'])), '请选择报价截止日期');
                check(!empty(trim($base['sup_scope'])), '请选择询价范围');
                check(!empty(trim($base['open_type'])), '请选择开标方式');
                check(!empty(trim($base['person_id'])), '请选择采购员');
                break;
            case 'change':
                check(!empty(trim($request['end_date'])), '请选择报价截止日期');
                check(trim($request['end_date']) < date('Y-m-d H:i:s'), '报价截止日期应大于当前时间');
        }
        return $next($request);
    }

}
