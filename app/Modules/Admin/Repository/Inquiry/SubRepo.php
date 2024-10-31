<?php

namespace App\Modules\Admin\Repository\Inquiry;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Inquiry\Sub,
    Quote\Quote
};
use App\Modules\Admin\Repository\{
    SupplierBaseRepo as SupplierRepo,
    UserRepo
};
use Illuminate\Support\Facades\Auth;

class SubRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Sub();
        parent::__construct($this->model);
    }

    public function info(int $inquiryId) {
        if (empty($inquiryId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('min_supplier_id,max_supplier_id,'
                . 'quote_num,min_sum_amount,max_sum_amount,avg_sum_amount,audit_remark,'
                . 'decider,decide_date,audit,terminate,opener,open_date,'
                . 'creator_id,create_time,modifier_id,modify_time,auditor_id,audit_date');
        $qurey->where('inquiry_id', $inquiryId);
        $object = $qurey->orderBy('id', 'ASC')->first();
        if (empty($object)) {
            return [];
        }
        $sub = $object->toArray();
        $sub['min_sum_amount'] = number_format($sub['min_sum_amount'], 2, '.', '');
        $sub['max_sum_amount'] = number_format($sub['max_sum_amount'], 2, '.', '');
        $sub['avg_sum_amount'] = number_format($sub['avg_sum_amount'], 2, '.', '');
        (new UserRepo())->setUser($sub, 'auditor_id', 'auditor_name');
        (new UserRepo())->setUser($sub, 'creator_id', 'creator_name');
        (new UserRepo())->setUser($sub, 'decider', 'decider_name');
        (new UserRepo())->setUser($sub, 'modifier_id', 'modifier_name');
        (new UserRepo())->setUser($sub, 'opener', 'opener_name');
        (new SupplierRepo)->setSupplier($sub, 'min_supplier_id', 'min_supplier_name');
        (new SupplierRepo)->setSupplier($sub, 'max_supplier_id', 'max_supplier_name');
        return $sub;
    }

    public function updateData(int $inquiryId) {
        $sub = $this->getSub($inquiryId);
        if (!empty($sub)) {
            Sub::upsert($sub, ['inquiry_id'], ['deleted_flag',
                'modify_time',
                'modifier_id',
                'quote_num',
                'min_supplier_id',
                'min_sum_amount',
                'max_sum_amount',
                'avg_sum_amount',
                'max_supplier_id']);
        }
    }

    public function getSub(int $inquiryId) {
        $admin = Auth::guard('admin')->user();
        $quoObj = Quote::selectRaw('id,sum_tax_amount,supplier_id')
                ->where('inquiry_id', $inquiryId)
                ->where('bill_status', 'C')
                ->where('deleted_flag', 'N')
                ->orderBy('bill_date', 'ASC')
                ->get();
        if (empty($quoObj)) {
            return[];
        }
        $quoteList = $quoObj->toArray();
        $quoteNum = count(array_unique(array_column($quoteList, 'supplier_id')));
        $sumAmountArr = [];
        $minSumAmount = $maxSumAmount = $maxSupplierId = $minSupplierId = 0;
        foreach ($quoteList as $quote) {
            $sumAmountArr[] = $quote['sum_tax_amount'];
            if ($minSumAmount === 0 || $minSumAmount > $quote['sum_tax_amount']) {
                $minSupplierId = $quote['supplier_id'];
            }
            if ($maxSumAmount === 0 || $maxSumAmount < $quote['sum_tax_amount']) {
                $maxSupplierId = $quote['supplier_id'];
            }
            $minSumAmount = $minSumAmount == 0 || $minSumAmount > $quote['sum_tax_amount'] ? $quote['sum_tax_amount'] : $minSumAmount;
            $maxSumAmount = $maxSumAmount == 0 || $maxSumAmount < $quote['sum_tax_amount'] ? $quote['sum_tax_amount'] : $maxSumAmount;
        }
        return[
            'inquiry_id' => $inquiryId,
            'creator_id' => $admin->user_id,
            'quote_num' => $quoteNum,
            'min_sum_amount' => $minSumAmount,
            'max_sum_amount' => $maxSumAmount,
            'avg_sum_amount' => !empty($sumAmountArr) && $quoteNum > 0 ? array_sum($sumAmountArr) / $quoteNum : 0,
            'min_supplier_id' => $minSupplierId,
            'max_supplier_id' => $maxSupplierId,
            'create_time' => date('Y-m-d H:i:s'),
            'deleted_flag' => 'N',
            'modifier_id' => $admin->user_id,
            'modify_time' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setQuoteNums(array &$list, string $field = 'id') {
        if (empty($list)) {
            return;
        }
        $inquiryIds = [];
        foreach ($list as &$val) {
            $val['quote_num'] = 0;
            $inquiryIds[] = $val[$field];
        }
        if (empty($inquiryIds)) {
            return $list;
        }
        $qurey = $this->model
                ->select('inquiry_id', 'quote_num');
        $qurey->whereIn('inquiry_id', $inquiryIds);

        $inquiryObjects = $qurey->get();
        if (empty($inquiryObjects)) {
            return $list;
        }
        $inquirys = $inquiryObjects->toArray();
        $inquiryArr = [];
        foreach ($inquirys as $inquiry) {
            $inquiryArr[$inquiry['inquiry_id']] = $inquiry['quote_num'];
        }
        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($inquiryArr[$val[$field]])) {
                $val['quote_num'] = $inquiryArr[$val[$field]];
            }
        }
    }

}
