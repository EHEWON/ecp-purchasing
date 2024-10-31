<?php

namespace App\Modules\Admin\Repository\Quote;

use App\Common\Contracts\Repository;
use App\Common\Models\Quote\{
    Quote,
    QuoteEntry,
    QuoteEntrySub
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EntrySubRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new QuoteEntrySub();
        parent::__construct($this->model);
    }

    public function decision(array $quoteIds, Request $request) {
        if (empty($request->entrys) || empty($quoteIds)) {
            return;
        }
        $base = $request->base;
        if (empty($base['bill_status']) || $base['bill_status'] !== 'C') {
            return;
        }
        $admin = Auth::guard('admin')->user();
        $materialIds = [];
        foreach ($request->entrys as $entry) {
            $materialIds[] = $entry;
        }
        $quoteTable = (new Quote)->getTable();
        $entryTable = (new QuoteEntry)->getTable();
        $quoteEntryList = Quote::from($quoteTable . ' as q')
                ->join($entryTable . ' as qe', function($join) {
                    $join->on('q.id', '=', 'qe.quote_id');
                })
                ->selectRaw('q.supplier_id,q.inquiry_id,qe.quote_id,q.inquiry_entry_id,qe.id,'
                        . 'qe.inquiry_unit_id,qe.material_name,qe.tax_rate_id')
                ->whereIn('q.id', $quoteIds)
                ->where('q.deleted_flag', 'N')
                ->whereIn('qe.inquiry_entry_id', $materialIds)
                ->get()
                ->toArray();
        $arr = [];
        foreach ($quoteEntryList as $entry) {
            $arr[$entry['supplier_id']][$entry['inquiry_entry_id']] = $entry;
        }
        $dataList = [];
        foreach ($request->entrys as $entry) {
            $sid = $entry['supplier_id'];
            $gid = $entry['inquiry_entry_id'];
            $quoteEntry = !empty($arr[$sid]) && !empty($arr[$sid][$gid]) ? $arr[$sid][$gid] : [];
            $dataList[] = [
                'quote_id' => !empty($quoteEntry) ? $quoteEntry['quote_id'] : 0,
                'entry_id' => !empty($quoteEntry) ? $quoteEntry['id'] : 0,
                'src_bill_id' => !empty($quoteEntry) ? $quoteEntry['inquiry_id'] : 0,
                'src_entry_id' => $gid,
                'sum_compare_qty' => !empty($entry['qty']) ? $entry['qty'] : 0,
                'sum_order_qty' => !empty($entry['qty']) ? $entry['qty'] : 0,
                'cfm_qty' => !empty($entry['qty']) ? $entry['qty'] : 0,
                'cfm_price' => !empty($entry['price']) ? $entry['price'] : 0,
                'cfm_tax_price' => !empty($entry['tax_price']) ? $entry['tax_price'] : 0,
                'cfm_tax_rate' => !empty($entry['tax_rate']) ? $entry['tax_rate'] : 0,
                'cfm_note' => !empty($entry['qty']) ? $entry['qty'] : 0,
                'cfm_tax_rate_id' => !empty($quoteEntry['tax_rate_id']) ? $quoteEntry['tax_rate_id'] : 0,
                'created_by' => $admin->user_id,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        QuoteEntrySub::insert($dataList);
    }

}
