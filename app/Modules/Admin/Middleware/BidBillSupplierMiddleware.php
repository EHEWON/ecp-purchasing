<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;

class BidBillSupplierMiddleware {

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
                if ($base['biz_type'] !== '2') {
                    return $next($request);
                }
                if (empty($request['suppliers'])) {
                    check(false, '请选择供应商');
                }
                $suppliers = $request['suppliers'];
                $supplierArr = [];
                $supplierIds = [];
                foreach ($suppliers as $supplier) {
                    if (empty(trim($supplier['supplier_id']))) {
                        continue;
                    }
                    if (in_array($supplier['supplier_id'], $supplierIds)) {
                        check(false, '请不要重复选择供应商');
                    }
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
                if ($base['biz_type'] !== '2') {
                    return $next($request);
                }
                if (empty($request['suppliers'])) {
                    check(false, '请选择供应商');
                }
                $suppliers = $request['suppliers'];
                $supplierArr = [];
                $supplierIds = [];
                foreach ($suppliers as $supplier) {
                    if (empty(trim($supplier['supplier_id']))) {
                        continue;
                    }
                    if (in_array($supplier['supplier_id'], $supplierIds)) {
                        check(false, '请不要重复选择供应商');
                    }
                    $supplierIds[] = $supplier['supplier_id'];
                    $supplierArr[] = $supplier;
                }
                check(!empty($supplierArr), '请选择供应商');
                break;
        }
        return $next($request);
    }

}
