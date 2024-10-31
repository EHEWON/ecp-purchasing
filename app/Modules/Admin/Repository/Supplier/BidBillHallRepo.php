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
    BidBill\SubRepo,
    BidBill\SupplierRepo AS BBSupplierRepo,
    SupplierBaseRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BidBillHallRepo extends Repository {

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

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'bill_date';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'bill_date') {
            $query->orderBy('bill_date', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = 'bb.id,bb.bill_no,'
    . 'bb.name,bb.bill_status,bb.org_id,bb.bill_date,bb.bill_status,bb.bid_status,bb.bid_number,'
    . 'bb.enroll_date,bb.open_date,bb.result_date,bb.sum_tax_amount,bb.created_by,bb.created_at') {

        $supplierTable = (new BidBillSupplier)->getTable();
        $supplierId = $this->getPSupplierId();
        $query = $this->model
                ->from($this->model->getTable() . ' as bb')
                ->join($supplierTable . ' as bs', function($join)use($supplierId) {
                    $join->on('bb.id', '=', 'bs.bid_bill_id')
                    ->where('bs.supplier_id', $supplierId);
                })
                ->selectRaw($filed);
        $query->where('bs.allow_bid', 1);
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        $repo = new BidBillRepo();
        foreach ($data as &$item) {
            $item['bill_status_name'] = $repo->getBillStatusText($item['bill_status']);
            $item['bid_status_name'] = $repo->getBidStatusText($item['bid_status']);
        }
        (new BBSupplierRepo)->setQuoteNums($data);
        (new SubRepo)->setSubs($data, 'id');
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    public function getWhere(&$query, Request $request) {
        $query->where('bb.bill_status', 'C');
        $query->whereIn('bb.bid_status', ['C', 'D', 'E', 'G', 'J', 'H']);
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('bb.name', 'like', '%' . $keyword . '%')
                        ->orWhere('bb.bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->bid_status)) {
            $bidStatus = $request->bid_status;
            $bidStatusies = is_array($bidStatus) ? $bidStatus : explode(',', trim($bidStatus));
            $query->whereIn('bb.bid_status', $bidStatusies);
        }


        if (!empty($request->bill_status)) {
            $billStatus = $request->bill_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('bb.bill_status', $billStatusies);
        }
        if (!empty($request->check_type)) {
            $query->where('bb.check_type', trim($request->check_type));
        }
        if (!empty($request->name)) {
            $query->where('bb.name', 'like', '%' . trim($request->name) . '%');
        }
        if (!empty($request->biz_type)) {
            $query->where('bb.biz_type', trim($request->biz_type));
        }

        if (!empty($request->statusies)) {
            $query->whereIn('bb.bid_status', $request->statusies);
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('bb.bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bb.bill_date', $createAts);
        }
    }

    /**
     * 竞价详情
     */
    public function hallInfo($id) {
        $bidbillObj = BidBill::where('id', $id)->first();
        if (empty($bidbillObj)) {
            check(false, '竞价不存在');
        }
        $base = $bidbillObj->toArray();
        $bidCount = $base['bid_count'];
        (new BBSupplierRepo)->setQuoteNum($base);
        if (!in_array($bidbillObj->bid_status, ['D', 'E'])) {
            if (!empty($bidbillObj->bid_number) && $bidbillObj->bid_number > $base['allow_num']) {
                check(false, '不满足供应商报名条件，不能进入竞价大厅');
            }
        }
        $supplierId = $this->getPSupplierId();
        $bidbillSupplierObj = BidBillSupplier::where('bid_bill_id', $id)
                ->where('supplier_id', $supplierId)
                ->first();
        if (empty($bidbillSupplierObj)) {
            check(false, '您没有报名改竞价');
        }
        if ($bidbillSupplierObj->allow_bid != 1) {
            check(false, '您没有被允许竞价');
        }
        $quoteObj = BidBillQuote::where('amount', '>', '0')
                ->where('bid_bill_id', $id)
                ->where('supplier_id', $supplierId)
                ->orderBy('quote_date', 'DESC')
                ->get();
        if (empty($quoteObj)) {
            $base['quote'] = [];
            $base['rank'] = [];
            return $base;
        }
        (new UserRepo)->setUser($base, 'person_id', 'person_name', 'open_date');
        $repo = new BidBillRepo();
        $base['bid_status_name'] = $repo->getBidStatusText($base['bid_status']);
        $quoteList = $quoteObj->toArray();
        foreach ($quoteList as &$quote) {
            $quote['amount'] = number_format($quote['amount'], 2, '.', '');
            $quote['reduceamt'] = number_format($quote['reduceamt'], 2, '.', '');
        }
        $lastQuote = BidBillQuote::from('bid_bill_quote as m1')
                ->selectRaw('ranking,amount,quote_date')
                ->where('bid_bill_id', $id)
                ->where('supplier_id', $supplierId)
                ->orderBy('quote_date', 'DESC')
                ->first();
        (new SupplierBaseRepo)->setSuppliers($quoteList);
        (new QuoteAttachRepo)->setQuoteAttachs($quoteList);
        $base['last_quote'] = $lastQuote;
        $base['sub'] = (new SubRepo)->info($id);
        $base['left_count'] = $bidCount - count($quoteList);
        $base['left_time'] = $this->getLeftTime($bidbillObj, $id);
        $base['quote'] = $quoteList;
        $base['rank'] = $this->getRank($quoteList);
        return $base;
    }

    public function getRank($quoteList) {
        $rank = [];
        foreach ($quoteList as $quote) {
            $rank[] = ['date' => $quote['quote_date'],
                'supplier_name' => $quote['supplier_name'],
                'amount' => $quote['amount']];
        }
        $quoteDate = array_column($rank, 'date');
        array_multisort(
                $quoteDate, SORT_ASC, SORT_STRING, $rank
        );
        return $rank;
    }

    public function getLeftTime($bidBill, $bidbillId, $lastQuoteDateTime = null) {
        if (empty($bidBill) || empty($bidBill->toArray())) {
            return;
        }
        $lastQuoteDate = $this->getLastQuoteDate($bidbillId, $lastQuoteDateTime);
        $sub = Sub::where('bid_bill_id', $bidbillId)->first();
        $bidTime = $bidBill->bid_time;
        $openDate = $bidBill->open_date;
        $lastTime = $bidBill->last_time;
        $delayTime = $bidBill->delay_time;
        $time = microtime(true);
        if ($bidBill->bid_status === 'H') {
            return $sub->bid_rest_at;
        } elseif (in_array($bidBill->bid_status, ['D', 'E', 'G', 'M'])) {
            return 0;
        }
        if (!empty($sub) && !empty($sub->pause_start_at)) {
            return $this->getPauseLeftTime($sub, $lastTime, $delayTime, $lastQuoteDate, $time);
        }
        $openTime = strtotime($openDate);
        $leftQuoteTime = 0;
        $leftTime = 0;
        if ($lastQuoteDate) {
            $lastQuoteTime = strtotime($lastQuoteDate);
            if (($lastQuoteTime + intval($lastTime * 60)) > $time) {
                $leftQuoteTime = $lastQuoteTime + $delayTime * 60 - $time;
            }
        }
        if ($openTime + $bidTime * 60 > $time) {
            $leftTime = $openTime + $bidTime * 60 - $time;
        }
        return !empty($lastQuoteTime) && $leftQuoteTime > $leftTime ? number_format($leftQuoteTime, 2, '.', '') : number_format($leftTime, 2, '.', '');
    }

    public function getPauseLeftTime($sub, $lastTime, $delayTime, $lastQuoteDate, $time) {
        $lastQuoteTime = !empty($lastQuoteDate) ? strtotime($lastQuoteDate) : 0;
        $openTime = strtotime($sub->pause_start_at);
        $bidTime = $sub->bid_rest_at;
        $leftQuoteTime = 0;
        $leftTime = 0;
        if (!empty($lastQuoteTime) && $lastQuoteTime + $lastTime * 60 > $time) {
            $leftQuoteTime = $lastQuoteTime + $delayTime * 60 - $time;
        }
        if ($openTime + $bidTime > $time) {
            $leftTime = $openTime + $bidTime - $time;
        }
        return !empty($lastQuoteTime) && $leftQuoteTime > $leftTime ? $leftQuoteTime : $leftTime;
    }

    public function getLastQuoteDate($bidbillId, $lastQuoteDate = null) {
        if (!empty($lastQuoteDate)) {
            return $lastQuoteDate;
        }
        return BidBillQuote::where('bid_bill_id', $bidbillId)
                        ->orderBy('quote_date', 'DESC')
                        ->value('quote_date');
    }

}
