<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use App\Common\Models\BidBill\{
    BidBill,
    BidBillPay,
    BidBillSupplier,
    Sub
};
use App\Modules\Admin\Repository\{
    BidBill\BidBillRepo,
    SupplierBaseRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BidBillPayRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new BidBillPay();
        parent::__construct($this->model);
    }

    //退款与缴费列表都用到此接口
    public function getList(int $bidBillId, $request = []) {
        if (empty($bidBillId)) {
            return [];
        }
        $bidBill = (new BidBill)->getTable();
        $supplier = (new BidBillSupplier)->getTable();
        $pay = (new BidBillPay)->getTable();
        $query = BidBillSupplier::from($supplier . ' as s')
                ->leftJoin($pay . ' AS ps', function ($join) {
                    $join->on('s.bid_bill_id', '=', 'ps.bid_bill_id')
                    ->on('s.supplier_id', '=', 'ps.supplier_id');
                })
                ->join($bidBill . ' as  bb', function($join) {
                    $join->on('bb.id', '=', 's.bid_bill_id');
                })
                ->selectRaw('ps.id as pay_id,bb.`name`,ps.bid_bill_id,s.supplier_id,ps.bill_status,bb.bid_status,'
                . 'ps.sure_amount,ps.real_amount,ps.pay_date,ps.remark,ps.return_certificate,ps.return_status,ps.return_certificate_name,'
                . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name');
        $query->where('s.bid_bill_id', $bidBillId);
        //退款与缴费列表都用到此接口不能直接加状态，需要前端传状态
        if (!empty($request->pay_status)) {
            $billStatus = $request->pay_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('ps.bill_status', $billStatusies);
        }
        if (!empty($request->return_status)) {
            $billStatus = $request->return_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('ps.return_status', $billStatusies);
        }
        $object = $query->orderBy('s.id', 'ASC')
                ->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new SupplierBaseRepo)->setSuppliers($data);
        foreach ($data as &$item) {
            $item['sure_amount'] = number_format($item['sure_amount'], 2, '.', '');
            $item['real_amount'] = number_format($item['real_amount'], 2, '.', '');
        }
        return $data;
    }

    public function payList(Request $request) {

        $bidBill = (new BidBill)->getTable();
        $supplier = (new BidBillSupplier)->getTable();
        $pay = (new BidBillPay)->getTable();
        $query = BidBillSupplier::from($supplier . ' as s')
                ->leftJoin($pay . ' AS ps', function ($join) {
                    $join->on('s.bid_bill_id', '=', 'ps.bid_bill_id')
                    ->on('s.supplier_id', '=', 'ps.supplier_id');
                })
                ->join($bidBill . ' as  bb', function($join) {
                    $join->on('bb.id', '=', 's.bid_bill_id');
                })
                ->selectRaw('ps.id,bb.org_id,ps.bid_bill_id,bb.`name`,bb.bill_no,s.supplier_id,'
                        . 'ps.bill_status AS pay_status,s.entry_status,ps.remark,'
                        . 'ps.sure_amount,ps.real_amount,ps.pay_date,ps.pay_id,bb.bid_status,'
                        . 's.return_id,s.return_date,ps.return_status,ps.return_certificate,ps.return_certificate_name,'
                        . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name')
                ->where('bb.deposit_flag', 'Y');

        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $query->orderBy('ps.pay_date', 'DESC');
        $object = $query->orderBy('s.id', 'ASC')->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();

        $bidBillRepo = new BidBillRepo();

        foreach ($data as &$item) {
            $item['sure_amount'] = number_format($item['sure_amount'], 2, '.', '');
            $item['real_amount'] = number_format($item['real_amount'], 2, '.', '');
            $item['pay_status_name'] = $this->getPayStatusText($item['pay_status']);
            $item['bid_status_name'] = $bidBillRepo->getBidStatusText($item['bid_status']);
            $item['return_status_name'] = $this->getReturnStatusText($item['return_status'], $item['pay_status']);
            $item['entry_status_name'] = $this->getEntryStatusText($item['entry_status']);
        }

        (new SupplierBaseRepo)->setSuppliers($data);
        (new SubRepo)->setSubs($data, 'id', false);
        (new UserRepo)->setUsers($data, 'pay_id', 'pay_name');
        (new UserRepo)->setUsers($data, 'return_id', 'return_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function info(int $id) {

        $bidBill = (new BidBill)->getTable();
        $supplier = (new BidBillSupplier)->getTable();
        $pay = (new BidBillPay)->getTable();
        $query = BidBillSupplier::from($supplier . ' as s')
                ->leftJoin($pay . ' AS ps', function ($join) {
                    $join->on('s.bid_bill_id', '=', 'ps.bid_bill_id')
                    ->on('s.supplier_id', '=', 'ps.supplier_id');
                })
                ->join($bidBill . ' as  bb', function($join) {
                    $join->on('bb.id', '=', 's.bid_bill_id');
                })
                ->selectRaw('ps.id,bb.id AS bid_bill_id,bb.org_id,bb.`name`,bb.bill_no,s.supplier_id,'
                        . 'ps.bill_status AS pay_status,s.entry_status,ps.remark,'
                        . 'ps.sure_amount,ps.real_amount,ps.pay_date,ps.pay_id,'
                        . 's.return_id,s.return_date,ps.return_status,ps.certificate_name,'
                        . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.return_certificate,ps.return_certificate_name')
                ->where('bb.deposit_flag', 'Y');
        $query->where('ps.id', $id);
        $object = $query->orderBy('s.id', 'ASC')
                ->first();
        if (empty($object)) {
            return [];
        }
        $item = $object->toArray();
        $item ['org_id'] = (string) $item ['org_id'];
        $item['sure_amount'] = number_format($item['sure_amount'], 2, '.', '');
        $item['real_amount'] = number_format($item['real_amount'], 2, '.', '');
        $item['pay_status_name'] = $this->getPayStatusText($item['pay_status']);
        $item['return_status_name'] = $this->getReturnStatusText($item['return_status'], $item['pay_status']);
        $item['entry_status_name'] = $this->getEntryStatusText($item['entry_status']);
        (new SupplierBaseRepo)->setSupplier($item);
        (new UserRepo)->setUser($item, 'pay_id', 'pay_name');
        return $item;
    }

    public function payAudit(int $id, Request $request) {
        $pay = BidBillPay::where('id', $id)->first();
        if (empty($pay)) {
            check(false, '付款信息不存在');
        }
        if (empty($pay->toArray())) {
            check(false, '付款信息不存在');
        }
        $BidBill = BidBill::where('id', $pay->bid_bill_id)->first();
        if (empty($BidBill)) {
            check(false, '竞价项目不存在');
        }
        if (empty($BidBill->toArray())) {
            check(false, '付款信息不存在');
        }
        if (empty($BidBill->toArray())) {
            check(false, '付款信息不存在');
        }
        if (empty($request->pay_status)) {
            check(false, '付款状态不能为空');
        }
        $admin = Auth::guard('admin')->user();
        BidBillPay::where('id', $id)->update([
            'bill_status' => $request->pay_status,
            'audited_by' => $admin->user_id,
            'audited_at' => date('Y-m-d H:i:s'),
        ]);
        $flag = BidBillSupplier::where('bid_bill_id', $pay->bid_bill_id)
                ->where('supplier_id', $pay->supplier_id)
                ->update([
            'entry_status' => $request->pay_status === 'C' ? 'D' : 'K',
            'pay_id' => $admin->user_id,
            'pay_date' => date('Y-m-d H:i:s'),
            'allow_bid' => $request->pay_status === 'C' ? '1' : '0',
        ]);
        $count = BidBillSupplier::where('bid_bill_id', $pay->bid_bill_id)
                ->where('entry_status', ['L', 'B', 'WQR'])
                ->count();
        if ($count > 0) {
            return $flag;
        }

        Sub::where('bid_bill_id', $pay->bid_bill_id)
                ->update([
//              'enroll_number' => $enrollNumber,
                    'cfm_id' => $admin->user_id,
                    'cfm_at' => date('Y-m-d H:i:s'),
        ]);

        BidBill::where('id', $pay->bid_bill_id)->update([
            'bid_status' => 'K',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id
        ]);
    }

    public function returnAudit(int $id, Request $request) {
        $pay = BidBillPay::where('id', $id)->first();
        if (empty($pay)) {
            check(false, '付款信息不存在');
        }
        if (empty($pay->toArray())) {
            check(false, '付款信息不存在');
        }
        $BidBill = BidBill::where('id', $pay->bid_bill_id)->first();
        if (empty($BidBill)) {
            check(false, '竞价项目不存在');
        }
        if (empty($BidBill->toArray())) {
            check(false, '退款信息不存在');
        }
        if (empty($BidBill->toArray())) {
            check(false, '退款信息不存在');
        }
        if (empty($request->pay_status)) {
            check(false, '退款状态不能为空');
        }
        $admin = Auth::guard('admin')->user();
        $flag = BidBillPay::where('id', $id)->update([
            'return_status' => 'F',
            'return_id' => $admin->user_id,
            'return_date' => date('Y-m-d H:i:s'),
            'return_certificate' => $request->return_certificate,
            'return_certificate_name' => $request->return_certificate_name,
            'audited_at' => date('Y-m-d H:i:s'),
        ]);
        if ($request->return_status !== 'F') {
            return $flag;
        }
        BidBillSupplier::where('bid_bill_id', $pay->bid_bill_id)
                ->where('supplier_id', $pay->supplier_id)
                ->update([
                    'entry_status' => 'E',
                    'return_id' => $admin->user_id,
                    'return_date' => date('Y-m-d H:i:s'),
        ]);
        return $flag;
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    public function getWhere(&$query, Request $request) {
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('bb.name', 'like', '%' . $keyword . '%')
                        ->orWhere('bb.bill_no', 'like', '%' . $keyword . '%');
            });
        }
        $query->whereNotIn('s.entry_status', ['L', 'T', 'WCY', 'N', 'C', 'A', 'H', 'Y']);
        if (!empty($request->person_name)) {
            $user = (new \App\Common\Models\User)->getTable();
            $personName = trim($request->person_name);
            $query->WhereRaw('EXISTS(SELECT u.user_id FROM ' . $user
                    . ' as u WHERE u.realname like \'%' . $personName . '%\''
                    . ' AND u.deleted_flag=\'N\' AND u.user_id=bb.person_id)');
        }
        if (!empty($request->entry_status)) {
            $billStatus = $request->entry_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('s.entry_status', $billStatusies);
        }
        if (!empty($request->pay_status)) {
            $billStatus = $request->pay_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('ps.bill_status', $billStatusies);
        }
        if (!empty($request->return_status)) {
            $billStatus = $request->return_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('ps.return_status', $billStatusies);
        }
        if (!empty($request->check_type)) {
            $query->where('bb.check_type', trim($request->check_type));
        }
        if (!empty($request->bid_status)) {
            $query->where('bb.bid_status', trim($request->bid_status));
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
        if (!empty($request->paytype)) {
            $createAts = $this->getTimeByType($request->paytype);
            $query->whereBetween('ps.pay_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('ps.pay_date', $createAts);
        }

        if (!empty($request->returntype)) {
            $createAts = $this->getTimeByType($request->returntype);
            $query->whereBetween('ps.return_date', $createAts);
        } elseif (!empty($request->returntime)) {
            $createtime = $request->returntime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('ps.return_date', $createAts);
        }
    }

    public function getPayStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '未缴费';
            case 'B':
                return '已缴费未确认';
            case 'C':
                return '已缴费已确认';
            case 'D':
                return '缴费已打回';
        }
    }

    public function getReturnStatusText($status, $payStatus = '') {
        switch (strtoupper($status)) {
            case 'E':
                return '退费中';
            case 'F':
                return '已退费';
            case 'N':
                if ($payStatus == 'C') {
                    return '未退费';
                } else {
                    return '无';
                }
        }
    }

    public function getEntryStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':return '待资审';
            case 'B':return '资审通过';
            case 'C':return '资审未通过';
            case 'D':return '保证金已审';
            case 'E':return '保证金已退';
            case 'F':return '已中标';
            case 'G':return '未中标';
            case 'H':return '报名截止';
            case 'J':return '未竞价';
            case 'K':return '保证金未收';
            case 'L':return '待缴费';
            case 'WQR':return '已缴费未确认';
            case 'M':return '竞价中';
            case 'N':return '未报名';
            case 'O':return '已缴费';
            case 'P':return '已暂停';
            case 'Q':return '评标中';
            case 'T':return '待报名';
            case 'WCY':return '未参与';
            case 'Y':return '已报名';
            case 'S':return '终止';
        }
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getBidBillPayNo($newNumber = null) {
        $prefix = 'JJJF';
        $qurey = $this->model->selectRaw('*');
        $billNo = $newNumber ? $newNumber : $qurey
                        ->where('bill_no', 'like', $prefix . '%')
                        ->orderBy('bill_no', 'DESC')
                        ->value('bill_no');
        if (!empty($billNo)) {
            $date = substr($billNo, 4, 8);
            $serialSetp = substr($billNo, 12, 5);
            $step = intval($serialSetp);
            $step ++;
            return $this->createSerialNo($step, $prefix, $date);
        }
        return$this->createSerialNo(1, $prefix, '');
    }

    /**
     * 生成流水号
     * @param string $step 需要补零的字符
     * @param string $prefix 前缀
     * @author liujf 2019-03-11
     * @return string $code
     */
    private function createSerialNo($step = 1, $prefix = '', $date = '') {
        $time = date('Ymd');
        if (empty($date) || $date < $time) {
            $step = 1;
        }
        $pad = str_pad($step, 5, '0', STR_PAD_LEFT);
        return$prefix . $time . $pad;
    }

}
