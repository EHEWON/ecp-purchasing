<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\Inquiry\{
    Entry,
    Inquiry
};

class SupplierQuoteEntryMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list($controller, $action) = explode('@', $currentRoute['uses']);
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

                $inquiry = Inquiry::where('id', $base['inquiry_id'])->selectRaw('total_inquiry,turns')->first();

                $entryCount = 0;
                if ($inquiry && $inquiry->total_inquiry) {
                    $entryCount = Entry::where('inquiry_id', $base['inquiry_id'])->whereRaw('FIND_IN_SET(' . $inquiry->turns . ',turns)')->count();
                }
                foreach ($entrys as $entry) {
                    if (empty($entry['tax_price']) && empty($entry['price'])) {
                        continue;
                    }
                    $entryArr[] = $entry;
                    check(!empty($entry['inquiry_entry_id']), '请选择询单商品');
                    check(!empty($entry['material_name']), '请输入物料名称');
                    check(!empty($entry['qty']), '请输入报价数量');
                    check(!empty($entry['quote_unit_id']), '请选择报价单位');
                    check(!empty($entry['tax_rate_id']), '请选择税率');
                    check(!empty($entry['tax_price']), '请输入含税单价');
                    check(!empty($entry['price']), '请输入单价');
//                    check(!empty($entry['warranty_period']), '请输入质保期');
                }
                check(!empty($entryArr), '请输入物料信息');
                if ($inquiry && $inquiry->total_inquiry) {
                    check($entryCount === count($entryArr), '整单询价要求所有商品都需要报价');
                }
                break;
            case 'edited':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                $inquiry = Inquiry::where('id', $base['inquiry_id'])->selectRaw('total_inquiry,turns')->first();

                $entryCount = 0;
                if ($inquiry && $inquiry->total_inquiry) {
                    $entryCount = Entry::where('inquiry_id', $base['inquiry_id'])->whereRaw('FIND_IN_SET(' . $inquiry->turns . ',turns)')->count();
                }
                $entrys = $request['entrys'];
                $entryArr = [];
                foreach ($entrys as $entry) {
                    if (empty($entry['tax_price']) && empty($entry['price'])) {
                        continue;
                    }
                    $entryArr[] = $entry;
                    check(!empty($entry['inquiry_entry_id']), '请选择询单商品');
                    check(!empty($entry['material_name']), '请输入物料名称');
                    check(!empty($entry['qty']), '请输入报价数量');
                    check(!empty($entry['quote_unit_id']), '请选择报价单位');
                    check(!empty($entry['tax_rate_id']), '请选择税率');
                    check(!empty($entry['tax_price']), '请输入含税单价');
                    check(!empty($entry['price']), '请输入单价');
//                    check(!empty($entry['warranty_period']), '请输入质保期');
                }
                check(!empty($entryArr), '请输入物料信息');
                if ($inquiry && $inquiry->total_inquiry) {
                    check($entryCount === count($entryArr), '整单询价要求所有商品都需要报价');
                }
                break;
        }
        return $next($request);
    }

}
