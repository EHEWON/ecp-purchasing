<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    BidBill\BidBill,
    BidBill\BidBillQuote,
    BidBill\BidBillSupplier,
    BidBill\Sub,
    UserSupplier
};
use App\Modules\Admin\Repository\{
    BidBill\QuoteAttachRepo,
    Supplier\BidBillHallRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BidBillQuoteRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'name',
        'biz_status',
        'bill_status',
        'org_id',
        'bill_date',
        'enroll_date',
        'open_date',
        'result_date',
        'created_at',
    ];
    protected $supplierId = null;
    protected $admin = null;
    protected $userId = null;

    public function __construct() {
        $this->model = new BidBill();
        parent::__construct($this->model);
        $this->admin = Auth::guard('admin')->user();
        if (empty($this->admin->user_type) || $this->admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $this->userId = $this->admin->user_id;
        $this->supplierId = UserSupplier::where('user_id', $this->userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($this->supplierId)) {
            check(false, '您没有关联供应商');
        }
    }

    /**     * **
     * 报价
     */
    public function updateData(int $bidBillId, Request $request) {

        $quote = $request->all();
        $bidBillObj = BidBill::where('id', $bidBillId)->first();
        if (empty($bidBillObj)) {
            check(false, '竞价不存在');
        }
        $bidBill = $bidBillObj->toArray();
        if ($bidBill['bid_status'] !== 'C') {
            check(false, '该竞价项目不在竞价中');
        }
        $supplierObj = BidBillSupplier::where('bid_bill_id', $bidBillId)
                ->where('supplier_id', $this->supplierId)
                ->first();
        if (empty($supplierObj)) {
            check(false, '未报名的供应商不能报价');
        }
        if ($bidBillObj->deposit_flag == 'Y' && $supplierObj->allow_bid !== '1') {
            check(false, '不允许竞价的供应商不能报价');
        }
        if (empty($quote['amount'])) {
            check(false, '请输入报价');
        }
        if (!empty($bidBill['max_amount']) && $quote['amount'] > $bidBill['max_amount']) {
            check(false, '报价须小于等于报价最高限额');
        }
        if (!empty($bidBill['min_amount']) && $quote['amount'] < $bidBill['min_amount']) {
            check(false, '报价须大于等于报价最低限额');
        }
        $scount = BidBillQuote::where('bid_bill_id', $bidBillId)
                ->where('amount', '>', '0')
                ->where('supplier_id', $this->supplierId)
                ->count();
        if (!empty($bidBill['bid_count']) && $scount >= $bidBill['bid_count']) {
            check(false, '报价须小于等于最多报价次数');
        }
        $isFreeQuote = $bidBill['is_free_quote'];
        $reduceType = $bidBill['reduce_type'];
        $reducepct = $bidBill['reducepct'];
        $quotationTrend = $bidBill['quotation_trend'];
        $lastAmount = BidBillQuote::from('bid_bill_quote as m1')
                ->where('bid_bill_id', $bidBillId)
                ->where('amount', '>', '0')
                ->where('supplier_id', $this->supplierId)
                ->orderBy('quote_date', 'DESC')
                ->value('amount');
        if (empty($isFreeQuote) && !empty($lastAmount) && in_array($quotationTrend, ['2', '3'])) {
            if ($reduceType == 'A' && $quotationTrend === '2' && round(($lastAmount - $quote['amount']) / $lastAmount, 2) < $reducepct / 100) {
                check(false, '报价须小于每次降价幅度');
            } elseif ($reduceType == 'A' && $quotationTrend === '3' && round(( $quote['amount'] - $lastAmount) / $lastAmount, 2) < $reducepct / 100) {

                check(false, '报价须小于每次加价幅度');
            } elseif ($reduceType == 'B' && $quotationTrend === '2' && round(($lastAmount - $quote['amount']), 2) < $reducepct) {

                check(false, '报价须小于每次降价幅度');
            } elseif ($reduceType == 'B' && $quotationTrend === '3' && round(( $quote['amount'] - $lastAmount), 2) < $reducepct) {

                check(false, '报价须小于每次加价幅度');
            }
        }
        $quoteDate = date('Y-m-d H:i:s');
        $entryData = [
            'bid_bill_id' => $bidBillId,
            'ranking' => 1,
            'supplier_id' => $this->supplierId,
            'amount' => !empty($quote['amount']) ? $quote['amount'] : 0,
            'reduceamt' => !empty($lastAmount) && $quote['amount'] ? number_format($lastAmount - $quote['amount'], 2, '.', '') : 0,
//            'bill_status' => 'B',
            'quote_date' => $quoteDate,
        ];
        $bidBillQuoteId = BidBillQuote::insertGetId($entryData);
        (new QuoteAttachRepo)->updateData($bidBillQuoteId, $request);
        wsSendMsg($bidBillId, 'quote', [
            'leftTime' => (new BidBillHallRepo)->getLeftTime($bidBillObj, $bidBillId),
            'lastQuoteTime' => $quoteDate,
            'message' => '',
        ]);

        $quoteTable = (new BidBillQuote)->getTable();
        $lastQuoteQuery = BidBillQuote::from($quoteTable . ' as ss')
                ->selectRaw('ss.bid_bill_id,ss.supplier_id,max(ss.quote_date) as max_quote_date')
                ->orderBy('ss.quote_date', 'desc')
                ->groupBy('ss.bid_bill_id')
                ->groupBy('ss.supplier_id');

        $quoteList = BidBillQuote::from('bid_bill_quote as m1')
                ->selectRaw('m1.id,m1.supplier_id,(SELECT COUNT(*) FROM bid_bill_quote m2 '
                        . 'INNER JOIN( SELECT ss.bid_bill_id, ss.supplier_id, max(ss.quote_date)AS max_quote_date'
                        . ' FROM `bid_bill_quote` AS `ss` GROUP BY `ss`.`bid_bill_id`, `ss`.`supplier_id` ORDER BY `ss`.`quote_date` DESC )AS `max2` '
                        . 'ON `m2`.`bid_bill_id` = `max2`.`bid_bill_id` AND `m2`.`quote_date` = `max2`.`max_quote_date` '
                        . 'AND `m2`.`supplier_id` = `max2`.`supplier_id`'
                        . 'WHERE  (m2.amount < m1.amount OR(m2.amount = m1.amount '
                        . 'AND m2.quote_date < m1.quote_date)) '
                        . 'AND m1.bid_bill_id = m2.bid_bill_id)+ 1 AS ranking,m1.amount')
                ->where('m1.bid_bill_id', $bidBillId)
                ->joinSub($lastQuoteQuery, 'max', function ($join) {
                    $join->on('m1.bid_bill_id', '=', 'max.bid_bill_id')
                    ->on('m1.quote_date', '=', 'max.max_quote_date')
                    ->on('m1.supplier_id', '=', 'max.supplier_id');
                })
                ->where('m1.amount', '>', '0')
                ->groupBy('m1.supplier_id')
                ->orderBy('m1.quote_date', 'DESC')
                ->get()
                ->toArray();
        BidBillQuote::upsert($quoteList, ['id'], ['ranking']);
        $bidBillSupplierIds = [];
        $bidBillSuppliers = [];
        foreach ($quoteList AS $quote) {
            if (in_array($quote['supplier_id'], $bidBillSupplierIds)) {
                continue;
            }
            $bidBillSuppliers[] = [
                'bid_bill_id' => $bidBillId,
                'ranking' => $quote['ranking'],
                'amount' => $quote['amount'],
                'supplier_id' => $quote['supplier_id'],
            ];
            $bidBillSupplierIds[] = $quote['supplier_id'];
        }
        Sub::where('bid_bill_id', $bidBillId)->update(['bid_num' => count($bidBillSupplierIds)]);
        if (!empty($bidBillSuppliers)) {
            BidBillSupplier::upsert($bidBillSuppliers, ['bid_bill_id', 'supplier_id'], ['ranking', 'amount']);
        }
        return $quoteList;
    }

    public function bindUid(int $bidBillId, Request $request) {
        $input = [
            'client_id' => $request->client_id,
            'user_id' => $this->userId,
            'bid_bill_id' => $bidBillId,
        ];
        return bindUid($input);
    }

    public function bindGroup(int $bidBillId, Request $request) {
        $input = [
            'client_id' => $request->client_id,
            'bid_bill_id' => $bidBillId,
        ];
        return bindGroup($input);
    }

    public function offline(int $bidBillId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $clientId = $request->client_id;
        $input = [
            'user_id' => $admin->user_id,
            'bid_bill_id' => $bidBillId,
            'client_id' => $clientId,
        ];
        return offline($input);
    }

}
