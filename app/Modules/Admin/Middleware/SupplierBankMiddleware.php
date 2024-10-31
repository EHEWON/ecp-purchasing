<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\Supplier;

class SupplierBankMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if ($request->status === Supplier::STATUS_DRAFT || $request->status === Supplier::STATUS_REVIEW) {
                    return $next($request);
                }
                if (empty($request->bank)) {
                    check(false, '银行信息不能为空');
                }
                $banks = [];
                foreach ($request->bank as $bank) {
                    if (empty($bank['bank_account']) && empty($bank['name'])) {
                        continue;
                    }
                    if (empty($bank['bank_account'])) {
                        check(false, '银行账号不能为空');
                    }
                    if (empty($bank['name'])) {
                        check(false, '账号名称不能为空');
                    }
                    $banks[] = $bank;
                }
                if (empty($banks)) {
                    check(false, '银行信息不能为空');
                }
                break;
            case 'edited':
                if ($request->status === Supplier::STATUS_DRAFT || $request->status === Supplier::STATUS_REVIEW) {
                    return $next($request);
                }
                if (empty($request->bank)) {
                    check(false, '银行信息不能为空');
                }
                $banks = [];
                foreach ($request->bank as $bank) {
                    if (empty($bank['bank_account']) && empty($bank['name'])) {
                        continue;
                    }
                    if (empty($bank['bank_account'])) {
                        check(false, '银行账号不能为空');
                    }
                    if (empty($bank['name'])) {
                        check(false, '账号名称不能为空');
                    }
                    $banks[] = $bank;
                }
                if (empty($banks)) {
                    check(false, '银行信息不能为空');
                }
                break;
        }
        return $next($request);
    }

}
