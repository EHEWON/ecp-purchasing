<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use Illuminate\Http\Request;
use App\Common\Models\BidBill\{
    BidBill,
    Sub
};
use App\Modules\Admin\Repository\{
    UserRepo,
    BidBill\SupplierRepo
};

class BidBillDecisionRepo extends Repository {

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
    public function getList(Request $request, $filed = 'bid_bill.id,bid_bill.bill_no,'
    . 'bid_bill.name,bid_bill.bill_status,bid_bill.org_id,bid_bill.bill_date,bid_bill.bill_status,bid_bill.bid_status,bid_bill.bid_number,'
    . 'bid_bill.enroll_date,bid_bill.open_date,bid_bill.result_date,bid_bill.sum_tax_amount,bid_bill.created_by,bid_bill.created_at') {
        $subTable = (new Sub)->getTable();
        $query = $this->model
                ->selectRaw($filed)
                ->leftJoin($subTable . ' AS sub', function ($join) {
            $join->on('bid_bill.id', '=', 'sub.bid_bill_id');
        });
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
        (new SupplierRepo)->setQuoteNums($data);
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
        $query->where('bid_bill.bill_status', 'C');
        $query->whereIn('bid_bill.bid_status', ['D', 'E', 'G']);
        $query->whereNot('bid_bill.cfm_status', 'null');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('bid_bill.name', 'like', '%' . $keyword . '%')
                        ->orWhere('bid_bill.bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->bid_status)) {
            $bidStatus = $request->bid_status;
            $bidStatusies = is_array($bidStatus) ? $bidStatus : explode(',', trim($bidStatus));
            $query->whereIn('bid_bill.bid_status', $bidStatusies);
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
            $query->whereIn('bid_bill.bill_status', $billStatusies);
        }
        if (!empty($request->check_type)) {
            $query->where('bid_bill.check_type', trim($request->check_type));
        }
        if (!empty($request->name)) {
            $query->where('bid_bill.name', 'like', '%' . trim($request->name) . '%');
        }
        if (!empty($request->biz_type)) {
            $query->where('bid_bill.biz_type', trim($request->biz_type));
        }

        if (!empty($request->statusies)) {
            $query->whereIn('bid_bill.bid_status', $request->statusies);
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('bid_bill.bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bid_bill.bill_date', $createAts);
        }
        if (!empty($request->opentype)) {
            $openAts = $this->getTimeByType($request->opentype);
            $query->whereBetween('bid_bill.open_date', $openAts);
        } elseif (!empty($request->opentime)) {
            $opentime = $request->opentime;
            $openAts = is_array($opentime) ? $opentime : explode(',', $opentime);
            !empty($openAts[1]) ? $openAts[1] = date('Y-m-d 23:59:59', strtotime($openAts[1])) : $openAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bid_bill.open_date', $openAts);
        }
        if (!empty($request->resultype)) {
            $resultAts = $this->getTimeByType($request->resultype);
            $query->whereBetween('bid_bill.result_date', $resultAts);
        } elseif (!empty($request->resulttime)) {
            $resulttime = $request->resulttime;
            $resultAts = is_array($resulttime) ? $resulttime : explode(',', $resulttime);
            !empty($resultAts[1]) ? $resultAts[1] = date('Y-m-d 23:59:59', strtotime($resultAts[1])) : $resultAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bid_bill.result_date', $resultAts);
        }
    }

}
