<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;

class InquiryEntryMiddleware {

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
                $entrys = $request['entrys'];
                $entryArr = [];
                foreach ($entrys as $entry) {
                    if (empty(trim($entry['material_name'])) && empty(trim($entry['inquire_qty'])) && empty(trim($entry['inquiry_unit_id']))) {
                        continue;
                    }
                    $entryArr[] = $entry;
                    check(!empty(trim($entry['material_name'])), '请输入物料名称');
                    check(!empty(trim($entry['inquire_qty'])), '请输入询价数量');
                    check(!empty(trim($entry['inquiry_unit_id'])), '请选择询价单位');
                }
                check(!empty($entryArr), '请输入物料信息');
                break;
            case 'edited':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                $entrys = $request['entrys'];
                $entryArr = [];
                foreach ($entrys as $entry) {
                    if (empty(trim($entry['material_name'])) && empty(trim($entry['inquire_qty'])) && empty(trim($entry['inquiry_unit_id']))) {
                        continue;
                    }
                    $entryArr[] = $entry;
                    check(!empty(trim($entry['material_name'])), '请输入物料名称');
                    check(!empty(trim($entry['inquire_qty'])), '请输入询价数量');
                    check(!empty(trim($entry['inquiry_unit_id'])), '请选择询价单位');
                }
                check(!empty($entryArr), '请输入物料信息');
                break;
            case 'enable':
            case 'disable':
            case 'delete':
                break;
        }
        return $next($request);
    }

}
