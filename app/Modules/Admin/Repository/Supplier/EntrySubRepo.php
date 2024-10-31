<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EntrySubRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new EntrySub();
        parent::__construct($this->model);
    }

    public function updateData(int $quoteId, $entrysub) {
        $admin = Auth::guard('admin')->user();
        $entrySubData = [
            'quote_id' => $quoteId,
            'entry_id' => !empty($entrysub['entry_id']) ? $entrysub['entry_id'] : null,
            'goods_id' => !empty($entrysub['goods_id']) ? $entrysub['goods_id'] : 0,
            'goods_desc' => !empty($entrysub['goods_desc']) ? $entrysub['goods_desc'] : null,
            'basic_unit_id' => !empty($entrysub['basic_unit_id']) ? $entrysub['basic_unit_id'] : 0,
            'basic_qty' => !empty($entrysub['basic_qty']) ? $entrysub['basic_qty'] : 0,
            'asst_unit_id' => !empty($entrysub['asst_unit_id']) ? $entrysub['asst_unit_id'] : 0,
            'asst_qty' => !empty($entrysub['asst_qty']) ? $entrysub['asst_qty'] : 0,
            'loc_amount' => !empty($entrysub['loc_amount']) ? $entrysub['loc_amount'] : 0,
            'loc_tax' => !empty($entrysub['loc_tax']) ? $entrysub['loc_tax'] : 0,
            'loc_taxamount' => !empty($entrysub['loc_taxamount']) ? $entrysub['loc_taxamount'] : 0,
            'act_price' => !empty($entrysub['act_price']) ? $entrysub['act_price'] : 0,
            'act_tax_price' => !empty($entrysub['act_tax_price']) ? $entrysub['act_tax_price'] : 0,
            'po_bill_id' => !empty($entrysub['po_bill_id']) ? $entrysub['po_bill_id'] : 0,
            'po_entry_id' => !empty($entrysub['po_entry_id']) ? $entrysub['po_entry_id'] : 0,
            'pc_bill_id' => !empty($entrysub['pc_bill_id']) ? $entrysub['pc_bill_id'] : 0,
            'pc_entry_id' => !empty($entrysub['pc_entry_id']) ? $entrysub['pc_entry_id'] : 0,
            'src_bill_type' => !empty($entrysub['src_bill_type']) ? $entrysub['src_bill_type'] : null,
            'src_bill_id' => !empty($entrysub['src_bill_id']) ? $entrysub['src_bill_id'] : 0,
            'src_entry_id' => !empty($entrysub['src_entry_id']) ? $entrysub['src_entry_id'] : 0,
            'sum_quote_qty' => !empty($entrysub['sum_quote_qty']) ? $entrysub['sum_quote_qty'] : null,
            'pr_bill_id' => !empty($entrysub['pr_bill_id']) ? $entrysub['pr_bill_id'] : 0,
            'pr_entry_id' => !empty($entrysub['pr_entry_id']) ? $entrysub['pr_entry_id'] : 0,
            'pr_bill_no' => !empty($entrysub['pr_bill_no']) ? $entrysub['pr_bill_no'] : null,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $admin->user_id,
        ];
        EntrySub::insert($entrySubData);
    }

    public function updateCompareSub(Request $request) {
        if (!empty($request->base['bill_status']) && $request->base['bill_status'] !== 'C') {
            return;
        }
        foreach ($request->quotes as $quote) {
            Supplier::where('supplier_id', $quote['supplier_id'])
                    ->where('quote_id', $quote['quote_id'])
                    ->update(['entry_status' => 'C',]);
        }
    }

}
