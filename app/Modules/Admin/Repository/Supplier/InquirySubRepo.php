<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Inquiry\Sub,
    Quote\Quote
};
use Illuminate\Support\Facades\Auth;

class InquirySubRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Sub();
        parent::__construct($this->model);
    }

    public function updateData(int $inquiryId) {
        $quoteList = Quote::selectRaw('supplier_id', 'sum_amount')
                ->where('inquiry_id', $inquiryId)
                ->where('deleted_flag', 'N')
                ->get()
                ->toArray();
        $inquiry = Inquiry::where('id', $inquiryId)
                ->where('deleted_flag', 'N')
                ->first()
                ->toArray();
        $sub = $this->getSub($inquiryId, $inquiry, $quoteList);
        if (!empty($sub)) {
            Sub::upsert($sub, ['inquiry_id'], ['deleted_flag',
                'modify_time',
                'min_supplier_id',
                'max_supplier_id',
                'inquiry_title']);
        }
    }

    public function getSub(int $inquiryId, $inquiry, $quoteList) {
        $admin = Auth::guard('admin')->user();
        $quoteNum = count($quoteList);
        $sumAmountArr = [];
        $minSumAmount = $maxSumAmount = $maxSupplierId = $minSupplierId = 0;
        foreach ($quoteList as $quote) {
            $sumAmountArr[] = $quote['amount'];
            if ($minSumAmount === 0 || $minSumAmount > $quote['amount']) {
                $minSupplierId = $quote['supplier_id'];
            }
            if ($maxSumAmount === 0 || $maxSumAmount < $quote['amount']) {
                $maxSupplierId = $quote['supplier_id'];
            }
            $minSumAmount = $minSumAmount == 0 || $minSumAmount > $quote['amount'] ? $quote['amount'] : $minSumAmount;
            $maxSumAmount = $maxSumAmount == 0 || $maxSumAmount < $quote['amount'] ? $quote['amount'] : $maxSumAmount;
        }

        return[
            'inquiry_id' => $inquiryId,
            'creator_id' => $admin->user_id,
            'origin' => !empty($inquiry['origin']) ? $inquiry['origin'] : '',
            'quote_num' => $quoteNum,
            'min_sum_amount' => !empty($sumAmountArr) ? min($sumAmountArr) : 0,
            'max_sum_amount' => !empty($sumAmountArr) ? max($sumAmountArr) : 0,
            'avg_sum_amount' => !empty($sumAmountArr) && $quoteNum > 0 ? array_sum($sumAmountArr) / $quoteNum : 0,
            'min_supplier_id' => !empty($minSupplierId) ? $minSupplierId : 0,
            'max_supplier_id' => !empty($minSupplierId) ? $minSupplierId : 0,
            'push_1688' => 0,
            'buy_offer_id' => '',
            'inquiry_title' => !empty($inquiry['title']) ? $inquiry['title'] : '',
            'create_time' => date('Y-m-d H:i:s'),
            'deleted_flag' => 'N',
            'modifier_id' => $admin->user_id,
            'modify_time' => date('Y-m-d H:i:s'),
            'auditor_id' => $admin->user_id,
            'audit_date' => date('Y-m-d H:i:s'),
            'deleted_flag' => 'N'
        ];
    }

}
