<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    BidBill\BidBill,
    BidBill\BidBillPay,
    BidBill\BidBillSupplier,
    Message,
    MessageReceiver,
    User,
    UserSupplier
};
use App\Modules\Admin\Repository\{
    SupplierBaseRepo,
    SupplierContactRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Mail\Message AS MailMessage;
use Illuminate\Support\Facades\{
    Auth,
    Mail
};

class BidBillPayRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new BidBillPay();
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

    protected function getOrder(&$query) {
        /**
         * 排序
         */
        $query->orderBy('ps.created_at', 'DESC');
    }

    public function getList(Request $request) {
        $bidBill = (new BidBill)->getTable();
        $supplier = (new BidBillSupplier)->getTable();
        $pay = (new BidBillPay)->getTable();
        $query = BidBillSupplier::from($supplier . ' as s')
                ->join($pay . ' AS ps', function ($join) {
                    $join->on('s.bid_bill_id', '=', 'ps.bid_bill_id')
                    ->on('s.supplier_id', '=', 'ps.supplier_id');
                })
                ->join($bidBill . ' as  bb', function($join) {
                    $join->on('bb.id', '=', 's.bid_bill_id');
                })
                ->selectRaw('bb.`name`,bb.id,s.supplier_id,ps.pay_id,ps.return_date,'
                . 'bb.cash_deposit as sure_amount,ps.real_amount,ps.return_status,'
                . 'ps.pay_date,bb.bill_no as bid_bill_no,ps.bill_status,ps.return_id,'
                . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name,`bb`.`org_id`,'
                . 'bb.check_type,bb.bid_status,ps.return_certificate,ps.return_certificate_name');
        $query->where('s.supplier_id', $this->supplierId)
                ->where('bb.deposit_flag', 'Y');
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query);
        $object = $query
                ->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }

        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['org_id'] = (string) $item['org_id'];
            if (empty($item['pay_date']) && empty($item['bill_status'])) {
                $item['bill_status'] = 'A';
            }
            $item['sure_amount'] = number_format($item['sure_amount'], 2, '.', '');
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            $item['return_status_name'] = $this->getReturnStatusText($item['return_status'], $item['bill_status']);
        }
        $bbsupplierRepor = new SupplierBaseRepo();
        (new $bbsupplierRepor)->setSuppliers($data);
        (new UserRepo)->setUsers($data, 'pay_id', 'pay_name');
        (new UserRepo)->setUsers($data, 'return_id', 'return_name');
        return ['data' => $data, 'total' => $total];
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
                $q->where('ps.bid_bill_name', 'like', '%' . $keyword . '%')
                        ->orWhere('ps.bid_bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->bid_bill_id)) {
            $bidBillId = trim($request->bid_bill_id);
            $query->where('ps.bid_bill_id', $bidBillId);
        }
        if (!empty($request->pay_status)) {
            $payStatus = trim($request->pay_status);
            $query->where('ps.bill_status', $payStatus);
        }
        if (!empty($request->return_status)) {
            $returnStatus = trim($request->return_status);
            $query->where('ps.return_status', $returnStatus);
        }

        if (!empty($request->paytype)) {
            $createAts = $this->getTimeByType($request->paytype);
            $query->whereBetween('ps.pay_date', $createAts);
        } elseif (!empty($request->pay_date)) {
            $createtime = $request->pay_date;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('ps.pay_date', $createAts);
        }

        if (!empty($request->returntype)) {
            $createAts = $this->getTimeByType($request->returntype);
            $query->whereBetween('ps.return_date', $createAts);
        } elseif (!empty($request->return_date)) {
            $createtime = $request->return_date;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('ps.return_date', $createAts);
        }
    }

    public function getBillStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '未缴费';
            case 'B':
                return '已缴费未确认';
            case 'C':
                return '已缴费已确认';
            case 'D':
                return '缴费已打回';
            case 'E':
                return '退费中';
            case 'N':
                return '未退费';
            case 'F':
                return '已退费';
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

    /**     * **
     * 缴纳保证金
     */
    public function updateData(int $bidBillId, Request $request) {

        $pay = $request->all();
        $bidBillObj = BidBill::where('id', $bidBillId)->first();
        if (empty($bidBillObj)) {
            check(false, '竞价不存在');
        }
        $bidBill = $bidBillObj->toArray();
        if ($bidBill['bill_status'] !== 'C') {
            check(false, '不是已审核的竞价不能缴费');
        }
//        if ($bidBill['bid_status'] === 'A') {
//            check(false, '报名中的竞价不能缴费');
//        }
//        if ($bidBill['check_type'] === '1' && in_array($bidBill['bid_status'], ['I'])) {
//            check(false, '未资审的竞价不能缴费');
//        }
        $supplierObj = BidBillSupplier::where('bid_bill_id', $bidBillId)
                ->where('supplier_id', $this->supplierId)
                ->first();
        if (empty($supplierObj)) {
            check(false, '未报名的竞价不能缴费');
        }
        if (!in_array($supplierObj->entry_status, ['L', 'WQR'])) {
            check(false, '我的状态不是待缴费的竞价不能缴费');
        }
        $contact = (new SupplierContactRepo)
                ->getDefaultContact($this->supplierId);
        $payinfo = BidBillPay::where('bid_bill_id', $bidBillId)
                ->where('supplier_id', $this->supplierId)
                ->first();

        if (!empty($payinfo) && $payinfo->bill_status == 'C') {
            check(false, '已缴费已确认的竞价不能修改缴费信息');
        }
        $payId = !empty($payinfo) ? $payinfo->id : null;
        $entryData = [
            'bill_no' => $this->getPayNo(),
            'bid_bill_id' => $bidBillId,
            'bid_bill_no' => $bidBill['bill_no'],
            'bid_bill_name' => $bidBill['name'],
            'org_id' => $bidBill['org_id'],
            'supplier_id' => $this->supplierId,
            'sure_amount' => $bidBill['cash_deposit'],
            'real_amount' => !empty($pay['real_amount']) ? $pay['real_amount'] : 0,
            'certificate_name' => !empty($pay['certificate'][0]['attach_name']) ? $pay['certificate'][0]['attach_name'] : '',
            'certificate' => !empty($pay['certificate'][0]['attach_url']) ? $pay['certificate'][0]['attach_url'] : '',
            'remark' => !empty($pay['remark']) ? $pay['remark'] : '',
            'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
            'contact_phone' => !empty($contact['phone']) ? $contact['phone'] : '',
            'contact_email' => !empty($contact['email']) ? $contact['email'] : '',
            'bill_status' => 'B',
            'pay_id' => $this->admin->user_id,
            'pay_date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->admin->user_id,
        ];
        $flag = !empty($payId) ? BidBillPay::where('id', $payId)->update($entryData) : BidBillPay::insertGetId($entryData, 'bid_bill_id');
        BidBillSupplier::where('bid_bill_id', $bidBillId)
                ->where('supplier_id', $this->supplierId)
                ->update([
                    'entry_status' => 'WQR',
        ]);
        if ($bidBillObj->bid_status === 'I' && $bidBillObj->check_type == '3' && $bidBillObj->deposit_flag == 'Y') {
            BidBill::where('id', $bidBillId)->update([
                'bid_status' => 'L',
            ]);
        } elseif ($bidBillObj->bid_status === 'B' && $bidBillObj->check_type == '1' && $bidBillObj->deposit_flag == 'Y') {
            BidBill::where('id', $bidBillId)->update([
                'bid_status' => 'L',
            ]);
        }
        $bossUrl = env('BOSS_URL');
        $this->sends($bossUrl, $bidBill);
        return $flag;
    }

    public function sends($bossUrl, $bidBill) {

        $messageId = Message::insertGetId([
                    'receiver_type' => 'PURCHASER',
                    'content_url' => $bossUrl . '/front/#/bidding/BiddingDetails?id=' . $bidBill['id'],
                    'sender_id' => 1,
                    'message_type' => 'SYSTEM',
                    'message_title' => '待收取保证金通知',
                    'message' => '【' . $bidBill['name'] . '】已有供应商缴费，请及时完成缴费确认。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);

        MessageReceiver::insert([
            'message_id' => $messageId,
            'receiver_id' => $bidBill['person_id'],
            'org_id' => $bidBill['org_id'],
            'read_flag' => 'N',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        if (empty($bidBill['email'])) {
            return;
        }

        $email = User::whereIn('user_id', $bidBill['person_id'])
                ->where('enable', '1')
                ->where('deleted_flag', 'N')
                ->value('email');
        if (empty($email)) {
            return;
        }
        $response = Mail::mailer('default')
                ->send('mail.bidbillPay', $bidBill, function (MailMessage $message) use ($email) {

            $message->to($email);

            $message->subject('【' . env('APP_NAME') . '】待收取保证金通知');
        });

        SendLog::insert([
            'type' => 'BIDBILL_BEABOUT',
            'message_to' => $bidBill['email'],
            'title' => '待收取保证金通知',
            'message' => json_encode($bidBill),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getPayNo() {
        $prefix = 'JJJF';
        $qurey = $this->model->selectRaw('*');
        $billNo = $qurey
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
                        . 's.return_id,s.return_date,ps.return_status,ps.return_certificate,ps.return_certificate_name,'
                        . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name')
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
        $item['return_status_name'] = $this->getReturnStatusText($item['return_status']);
        $item['entry_status_name'] = $this->getEntryStatusText($item['entry_status']);
        (new SupplierBaseRepo)->setSupplier($item);
        (new UserRepo)->setUser($item, 'pay_id', 'pay_name');
        return $item;
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

    public function setPayStatus(&$list, $supplierId) {

        if (empty($list)) {
            return;
        }
        $bidbillIds = [];
        foreach ($list as &$item) {
            $item['pay_status'] = '';
            $bidbillIds[] = $item['id'];
        }
        $payObj = BidBillPay::whereIn('bid_bill_id', $bidbillIds)
                ->where('supplier_id', $supplierId)
                ->selectRaw('bid_bill_id,bill_status')
                ->get();
        if (empty($payObj) || empty($payObj->toArray())) {
            return;
        }
        $payList = $payObj->toArray();
        $payStatusArr = array_column($payList, 'bill_status', 'bid_bill_id');
        foreach ($list as &$item) {
            if (!empty($payStatusArr[$item['id']])) {
                $item['pay_status'] = $payStatusArr[$item['id']];
            }
        }
    }

}
