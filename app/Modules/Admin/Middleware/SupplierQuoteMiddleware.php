<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\UserSupplier;
use App\Common\Models\Inquiry\Inquiry;
use App\Common\Models\Quote\Quote;
use App\Common\Models\Inquiry\Supplier;
use Illuminate\Support\Facades\Auth;

class SupplierQuoteMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                $admin = Auth::guard('admin')->user();
                if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
                    check(false, '您不是供应商用户');
                }
                $userId = $admin->user_id;
                $supplierId = UserSupplier::where('user_id', $userId)
                        ->where('deleted_flag', 'N')
                        ->value('supplier_id');
                if (empty($supplierId)) {
                    check(false, '您没有关联供应商');
                }
                if (empty($request->base)) {
                    check(false, '报价基本信息不能为空');
                }
                $base = $request->base;
                check(!empty($base['inquiry_id']), '请选择询价单');
                $inquiry = Inquiry::where('id', $base['inquiry_id'])->first();
                $request->merge(['total_inquiry' => $request->total_inquiry]);
                if ($inquiry->sup_scope === 2) {
                    $count = Supplier::where('inquiry_id', $base['inquiry_id'])
                            ->where('supplier_id', $supplierId)
                            ->where('deleted_flag', 'N')
                            ->count();
                    check($count > 1, '您不是指定供应商');
                }
                if ($base['bill_status'] !== 'C') {
                    return $next($request);
                }
                check(!empty($base['delivery_date']), '交货期描述不能为空');
                check(!empty($base['payment_terms']), '付款条件不能为空');
                break;
            case 'edited':
                $admin = Auth::guard('admin')->user();
                if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
                    check(false, '您不是供应商用户');
                }
                $userId = $admin->user_id;
                $supplierId = UserSupplier::where('user_id', $userId)
                        ->where('deleted_flag', 'N')
                        ->value('supplier_id');
                if (empty($supplierId)) {
                    check(false, '您没有关联供应商');
                }
                $base = $request->base;
                $quoteId = $request->id;
                check(!empty($base['inquiry_id']), '请选择询价单');
                $quote = Quote::where('id', $quoteId)
                        ->selectRaw('bill_status,supplier_id')
                        ->where('deleted_flag', 'N')
                        ->first();
                if (empty($quote)) {
                    check(false, '报价单不存在');
                }
                if ($quote->supplier_id !== $supplierId) {
                    check(false, '您不是该报价单的所有者');
                }

                $inquiry = Inquiry::where('id', $base['inquiry_id'])->first();
                $request->merge(['total_inquiry' => $request->total_inquiry]);
                if ($inquiry->sup_scope === 2) {
                    $count = Supplier::where('inquiry_id', $base['inquiry_id'])
                            ->where('supplier_id', $supplierId)
                            ->whereIn('supplier_biz_status', ['A', 'B'])
                            ->where('deleted_flag', 'N')
                            ->count();
                    check($count > 1, '您不是指定供应商');
                }
                if ($base['bill_status'] !== 'C') {
                    return $next($request);
                }
                check(!empty($base['delivery_date']), '交货期描述不能为空');
                check(!empty($base['payment_terms']), '付款条件不能为空');
                break;
        }
        return $next($request);
    }

}
