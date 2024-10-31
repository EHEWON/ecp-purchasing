<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;

class BidBillEntryMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                $entrys = $request['entrys'];
                foreach ($entrys as $entry) {
                    if (!empty(trim($entry['qty'])) && $entry['qty'] <= '0') {
                        check(false, '竞价商品数量必须大于0');
                    }
                    if (!empty(trim($entry['price'])) && $entry['price'] < '0') {
                        check(false, '竞价商品价格必须大于等于0');
                    }
                    if (!empty(trim($entry['tax_price'])) && $entry['tax_price'] < '0') {
                        check(false, '基准含税单价必须大于0');
                    }
                    if (!empty(trim($entry['amount'])) && $entry['amount'] < '0') {
                        check(false, '金额必须大于等于0');
                    }
                    if (!empty(trim($entry['tax'])) && $entry['tax'] < '0') {
                        check(false, '税额必须大于0');
                    }
                    if (!empty(trim($entry['tax_amount'])) && $entry['tax_amount'] < '0') {
                        check(false, '价税合计必须大于等于0');
                    }
                }
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                $entryArr = [];
                foreach ($entrys as $entry) {
                    if (empty(trim($entry['material_name'])) && empty(trim($entry['qty'])) && empty(trim($entry['unit_id']))) {
                        continue;
                    }
                    $entryArr[] = $entry;
                    check(!empty(trim($entry['material_name'])), '请输入物料名称');
                    check(!empty(trim($entry['qty'])), '请输入数量');
                    check(!empty(trim($entry['unit_id'])), '请选择单位');
                    check(!empty(trim($entry['tax_price'])), '请输入基准含税单价');
                }
                check(!empty($entryArr), '请输入物料信息');
                break;
            case 'edited':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                $entrys = $request['entrys'];
                foreach ($entrys as $entry) {
                    if (!empty(trim($entry['qty'])) && $entry['qty'] <= '0') {
                        check(false, '竞价商品数量必须大于0');
                    }
                    if (!empty(trim($entry['price'])) && $entry['price'] < '0') {
                        check(false, '竞价商品价格必须大于等于0');
                    }
                    if (!empty(trim($entry['tax_price'])) && $entry['tax_price'] < '0') {
                        check(false, '基准含税单价必须大于0');
                    }
                    if (!empty(trim($entry['amount'])) && $entry['amount'] < '0') {
                        check(false, '金额必须大于等于0');
                    }
                    if (!empty(trim($entry['tax'])) && $entry['tax'] < '0') {
                        check(false, '税额必须大于0');
                    }
                    if (!empty(trim($entry['tax_amount'])) && $entry['tax_amount'] < '0') {
                        check(false, '价税合计必须大于等于0');
                    }
                }
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                $entryArr = [];
                foreach ($entrys as $entry) {
                    if (empty(trim($entry['material_name'])) && empty(trim($entry['qty'])) && empty(trim($entry['unit_id']))) {
                        continue;
                    }
                    $entryArr[] = $entry;
                    check(!empty(trim($entry['material_name'])), '请输入物料名称');
                    check(!empty(trim($entry['qty'])), '请输入数量');
                    check(!empty(trim($entry['unit_id'])), '请选择单位');
                    check(!empty(trim($entry['tax_price'])), '请输入基准含税单价');
                }
                check(!empty($entryArr), '请输入物料信息');
                break;
        }
        return $next($request);
    }

}
