<?php

namespace App\Modules\Admin\Repository\Quote;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Inquiry\Supplier,
    Quote\Quote,
    Quote\QuoteSub
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new QuoteSub();
        parent::__construct($this->model);
    }

    public function decision(int $compareId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $base = $request->base;
        if (empty($base['bill_status']) || $base['bill_status'] !== 'C') {
            return;
        }
        $inquiryId = $base['inquiry_id'];
        $quoteCount = count($request->quotes);
        $quoteIds = [];
        $subList = [];
        foreach ($request->quotes as $quote) {
            $quoteIds[] = $quote['quote_id'];
            Quote::where('id', $quote['quote_id'])
                    ->where('deleted_flag', 'N')
                    ->update(['biz_status' => $quoteCount === 1 ? 'C' : 'D']);
            Supplier::where('quote_id', $quote['quote_id'])
                    ->update(['entry_status' => $quoteCount === 1 ? 'C' : 'D']);
            $subList[] = [
                'quote_id' => $quote['quote_id'],
                'create_time' => date('Y-m-d H:i:s'),
                'creator_id' => $admin->user_id,
                'cfm_id' => $compareId,
                'cfm_date' => date('Y-m-d H:i:s'),
                'sup_name' => '',
                'open_type' => '',
                'contact_way' => '',
                'contact_or' => '',
                'quote_from' => '',
            ];
        }
        QuoteSub::insert($subList);
        Quote::where('inquiry_id', $inquiryId)
                ->where('deleted_flag', 'N')
                ->whereNotIn('id', $quoteIds)
                ->update(['biz_status' => 'E']);
        Supplier::where('inquiry_id', $inquiryId)->whereNotIn('id', $quoteIds)
                ->update(['entry_status' => 'E']);
        (new EntrySubRepo)->decision($compareId, $request);
    }

}
