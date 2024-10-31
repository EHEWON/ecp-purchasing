<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use App\Common\Models\BidBill\{
    BidBill,
    BidBillQuote,
    Sub
};
use App\Modules\Admin\Repository\{
    BidBill\QuoteAttachRepo,
    BidBill\SubRepo,
    BidBill\SupplierRepo AS BidBillSupplierRepo,
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
    public function getList(Request $request, $filed = 'id,bill_no,'
    . 'name,bill_status,org_id,bill_date,bill_status,bid_status,bid_number,'
    . 'enroll_date,open_date,result_date,sum_tax_amount,created_by,created_at') {
        $query = $this->model
                ->selectRaw($filed);
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
        (new SubRepo)->setSubs($data, 'id');
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new BidBillSupplierRepo)->setQuoteNums($data);
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
        $query->where('bill_status', 'C');
        $query->whereIn('bid_status', ['C', 'D', 'E', 'G', 'J', 'H']);
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->bid_status)) {
            $bidStatus = $request->bid_status;
            $bidStatusies = is_array($bidStatus) ? $bidStatus : explode(',', trim($bidStatus));
            $query->whereIn('bid_status', $bidStatusies);
        }

        if (!empty($request->person_name)) {
            $user = (new \App\Common\Models\User)->getTable();
            $personName = trim($request->person_name);
            $query->WhereRaw('EXISTS(SELECT u.user_id FROM ' . $user
                    . ' as u WHERE u.realname like \'%' . $personName . '%\''
                    . ' AND u.deleted_flag=\'N\' AND u.user_id=inquiry.person_id)');
        }
        if (!empty($request->bill_status)) {
            $billStatus = $request->bill_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('bill_status', $billStatusies);
        }
        if (!empty($request->check_type)) {
            $query->where('check_type', trim($request->check_type));
        }
        if (!empty($request->name)) {
            $query->where('name', 'like', '%' . trim($request->name) . '%');
        }
        if (!empty($request->biz_type)) {
            $query->where('biz_type', trim($request->biz_type));
        }

        if (!empty($request->statusies)) {
            $query->whereIn('bid_status', $request->statusies);
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bill_date', $createAts);
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
        (new BidBillSupplierRepo)->setQuoteNum($base);
        if (empty($bidbillObj->cfm_status)) {
            check(false, '不满足竞价启动条件');
        }
        if (!in_array($bidbillObj->bid_status, ['D', 'E'])) {
            if (!empty($bidbillObj->bid_number) && $bidbillObj->bid_number > $base['allow_num']) {
                check(false, '不满足供应商报名条件，不能进入竞价大厅');
            }
        }
        $quoteObj = BidBillQuote::where('amount', '>', '0')
                ->where('bid_bill_id', $id)
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
        (new SupplierBaseRepo)->setSuppliers($quoteList);
        (new QuoteAttachRepo)->setQuoteAttachs($quoteList);

        $base['quote'] = $quoteList;
        $base['rank'] = $this->getRank($quoteList);
        $base['sub'] = (new SubRepo)->info($id);
        $base['left_time'] = $this->getLeftTime($bidbillObj, $id);
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

    public function bindUid(int $bidBillId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $input = [
            'client_id' => $request->client_id,
            'user_id' => $admin->user_id,
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
        offline($input);
    }

    public function getLeftTime($bidBill, $bidbillId, $lastQuoteDateTime = null) {
        if (empty($bidBill)) {
            return;
        }
        if (is_object($bidBill) && empty($bidBill->toArray())) {
            return;
        }
        $sub = Sub::where('bid_bill_id', $bidbillId)->first();
        if ($bidBill->bid_status === 'H') {
            return $sub->bid_rest_at;
        } elseif (in_array($bidBill->bid_status, ['D', 'E', 'G', 'M'])) {
            return 0;
        }
        $lastQuoteDate = $this->getLastQuoteDate($bidbillId, $lastQuoteDateTime);
        $bidTime = $bidBill->bid_time;
        $openDate = $bidBill->open_date;
        $lastTime = $bidBill->last_time;
        $delayTime = $bidBill->delay_time;
        $time = microtime(true);
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
