<?php

namespace App\Modules\Admin\Middleware\Project;

use Closure;
use Illuminate\Http\Request;

class ProjectSupplierMiddleware {

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
                if ($base['bid_mode_id'] !== '2') {
                    return $next($request);
                }
                if (empty($request['supplier'])) {
                    check(false, '请选择供应商');
                }
                $suppliers = $request['supplier'];
                $supplierArr = [];
                $supplierIds = [];
                foreach ($suppliers as $supplier) {
                    if (!isset($supplier['supplier_id']) || empty(trim($supplier['supplier_id']))) {
                        continue;
                    }
                    if (in_array($supplier['supplier_id'], $supplierIds)) {
                        check(false, '请不要重复选择供应商');
                    }
//                    if (!empty($base['charging_stage']) && $base['charging_stage'] == '2') {
//                        if (empty($base['is_supplier_get']) || $base['is_supplier_get'] == 'Y') {
//                            check(!empty($supplier['supplier_deposit']) && $supplier['supplier_deposit'] > 0, '供应商投标保证金须大于0');
//                        }
//                    }
                    $supplierIds[] = $supplier['supplier_id'];
                    $supplierArr[] = $supplier;
                }
                check(!empty($supplierArr), '请选择供应商');
                break;
            case 'edited':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                if ($base['bid_mode_id'] !== '2') {
                    return $next($request);
                }
                if (empty($request['supplier'])) {
                    check(false, '请选择供应商');
                }
                $suppliers = $request['supplier'];
                $supplierArr = [];
                $supplierIds = [];
                foreach ($suppliers as $supplier) {
                    if (!isset($supplier['supplier_id']) || empty(trim($supplier['supplier_id']))) {
                        continue;
                    }
                    if (in_array($supplier['supplier_id'], $supplierIds)) {
                        check(false, '请不要重复选择供应商');
                    }
//                    if (!empty($base['charging_stage']) && $base['charging_stage'] == '2') {
//                        if (empty($base['is_supplier_get']) || $base['is_supplier_get'] == 'Y') {
//                            check(!empty($supplier['supplier_deposit']) && $supplier['supplier_deposit'] > 0, '供应商投标保证金须大于0');
//                        }
//                    }
                    $supplierIds[] = $supplier['supplier_id'];
                    $supplierArr[] = $supplier;
                }
                check(!empty($supplierArr), '请选择供应商');
                break;
        }
        return $next($request);
    }

}
