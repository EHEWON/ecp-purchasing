<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Message,
    MessageReceiver,
    Project\Project,
    Project\ProjectPay,
    Project\ProjectSub,
    Project\ProjectSupplier,
    User,
    UserSupplier
};
use App\Modules\Admin\Repository\{
    SupplierBaseRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Mail\Message AS MailMessage;
use Illuminate\Support\Facades\{
    Auth,
    Mail
};

class ProjectPayRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectPay();
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

    public function getListByProject($projectId, $request = []) {
        if (empty($projectId)) {
            return [];
        }
        $project = (new Project)->getTable();
        $supplier = (new ProjectSupplier)->getTable();
        $pay = (new ProjectPay)->getTable();
        $query = ProjectSupplier::from($supplier . ' as s')
                ->join($pay . ' AS ps', function ($join) {
                    $join->on('s.project_id', '=', 'ps.project_id')
                    ->on('s.supplier_id', '=', 'ps.supplier_id');
                })
                ->join($project . ' as  bb', function($join) {
                    $join->on('bb.id', '=', 's.project_id');
                })
                ->selectRaw('bb.`name`,bb.id as project_id,s.supplier_id,ps.id,ps.return_date,'
                . 'ps.sure_amount,ps.real_amount,ps.return_status,'
                . 'ps.pay_date,bb.bill_no as project_no,ps.bill_status,ps.return_id,'
                . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name,`bb`.`org_id`,'
                . 'ps.return_certificate,ps.return_certificate_name,ps.type,bb.bid_open_deadline,ps.remark');
        $query->where('ps.project_id', $projectId);
        $query->where('s.supplier_id', $this->supplierId);
        $this->getWhere($query, $request);
        $this->getPage($query, $request);
        $this->getOrder($query);
        $clone = $query->clone();
        $total = $clone->count();
        $object = $query->get();
        $data = $object->toArray();
        if (empty($data)) {
            $project_sub = ProjectSub::where('project_id', $projectId)->first();
            $project_info = Project::where('id', $projectId)->first();
            $i = 0;
            if ($project_sub['deposit'] != 0) {
                $data[$i]['bid_open_deadline'] = $project_info['bid_open_deadline'];
                $data[$i]['real_amount'] = '';
                $data[$i]['pay_date'] = '';
                $data[$i]['certificate_name'] = '';
                $data[$i]['certificate'] = '';
                $data[$i]['remark'] = '';
                $data[$i]['supplier_id'] = $this->supplierId;
                $data[$i]['type'] = 'EARNEST';
                $data[$i]['bill_status'] = 'A';
                $data[$i]['bill_status_name'] = $this->getBillStatusText('A');
                $data[$i]['sure_amount'] = number_format($project_sub['deposit'], 2, '.', '');
                $i++;
            }
            if ($project_sub['tender_fee'] != 0) {
                $data[$i]['bid_open_deadline'] = $project_info['bid_open_deadline'];
                $data[$i]['real_amount'] = '';
                $data[$i]['pay_date'] = '';
                $data[$i]['certificate_name'] = '';
                $data[$i]['remark'] = '';
                $data[$i]['certificate'] = '';
                $data[$i]['supplier_id'] = $this->supplierId;
                $data[$i]['type'] = 'DOCUMENT';
                $data[$i]['bill_status'] = 'A';
                $data[$i]['bill_status_name'] = $this->getBillStatusText('A');
                $data[$i]['sure_amount'] = number_format($project_sub['tender_fee'], 2, '.', '');
            }

            return ['data' => $data];
        }
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
        if ($total == 1) {
            $project_sub = ProjectSub::where('project_id', $projectId)->first();
            $project_info = Project::where('id', $projectId)->first();
            $data[1]['bid_open_deadline'] = $project_info['bid_open_deadline'];
            $data[1]['real_amount'] = '';
            $data[1]['pay_date'] = '';
            $data[1]['certificate_name'] = '';
            $data[1]['certificate'] = '';
            $data[1]['remark'] = '';
            $data[1]['supplier_id'] = $this->supplierId;
            $data[1]['bill_status'] = 'A';
            $data[1]['bill_status_name'] = $this->getBillStatusText($data[1]['bill_status']);
            if ($data[0]['type'] == 'EARNEST') {
                $data[1]['type'] = 'DOCUMENT';
                $data[1]['sure_amount'] = number_format($project_sub['tender_fee'], 2, '.', '');
            } else {
                $data[1]['type'] = 'EARNEST';
                $data[1]['sure_amount'] = number_format($project_sub['deposit'], 2, '.', '');
            }
            if ($data[1]['sure_amount'] == 0.00) {
                unset($data[1]);
            }
        }
        return ['data' => $data];
    }

    public function getList(Request $request) {
        $project = (new Project)->getTable();
        $supplier = (new ProjectSupplier)->getTable();
        $pay = (new ProjectPay)->getTable();
        $query = ProjectSupplier::from($supplier . ' as s')
                ->join($pay . ' AS ps', function ($join) {
                    $join->on('s.project_id', '=', 'ps.project_id')
                    ->on('s.supplier_id', '=', 'ps.supplier_id');
                })
                ->join($project . ' as  bb', function($join) {
                    $join->on('bb.id', '=', 's.project_id');
                })
                ->selectRaw('bb.`name`,bb.id as project_id,s.supplier_id,ps.id,ps.return_date,'
                . 'ps.sure_amount,ps.real_amount,ps.return_status,'
                . 'ps.pay_date,bb.bill_no as project_no,ps.bill_status,ps.return_id,'
                . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name,`bb`.`org_id`,'
                . 'ps.return_certificate,ps.return_certificate_name,ps.type,bb.bid_open_deadline,ps.remark');
        $query->where('s.supplier_id', $this->supplierId);
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
                $q->where('bb.name', 'like', '%' . $keyword . '%')
                        ->orWhere('ps.bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->project_id)) {
            $project_id = trim($request->project_id);
            $query->where('ps.project_id', $project_id);
        }
        if (!empty($request->bill_status)) {
            $payStatus = trim($request->bill_status);
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
    public function edit($request) {
        $paylist = $request->data;
        foreach ($paylist as $item) {
            $projectObj = Project::where('id', $item['project_id'])->first();
            $projectSubObj = ProjectSub::where('project_id', $item['project_id'])->first();
            $projectSupplierObj = ProjectSupplier::where('project_id', $item['project_id'])->where('supplier_id', $this->supplierId)->first();
            if (empty($projectObj)) {
                check(false, '招标不存在');
            }
            $payinfo = ProjectPay::where('project_id', $item['project_id'])->where('type', $item['type'])->where('supplier_id', $this->supplierId)->first();
            if (empty($item['type'])) {
                check(false, '缴费类型不能为空');
            }
            if (empty($projectSupplierObj) || $projectSupplierObj['shortlist_flag'] != 'Y') {
                check(false, '未入围无需缴费');
            }
            $time = date('Y-m-d H:i:s');
            if ($projectObj['bid_open_deadline'] < $time) {
                check(false, '缴费时间已截止');
            }
            if (empty($payinfo) || $payinfo->bill_status != 'C') {
                $payId = !empty($payinfo) ? $payinfo->id : null;
                $entryData = [
                    'project_id' => $item['project_id'],
                    'project_no' => $projectObj['bill_no'],
                    'project_name' => $projectObj['name'],
                    'org_id' => $projectObj['org_id'],
                    'supplier_id' => $this->supplierId,
                    'type' => !empty($item['type']) ? $item['type'] : '',
                    'real_amount' => !empty($item['real_amount']) ? $item['real_amount'] : 0,
                    'certificate_name' => !empty($item['certificate'][0]['attach_name']) ? $item['certificate'][0]['attach_name'] : '',
                    'certificate' => !empty($item['certificate'][0]['attach_url']) ? $item['certificate'][0]['attach_url'] : '',
                    'remark' => !empty($item['remark']) ? $item['remark'] : '',
                    'contact_name' => !empty($item['contact_name']) ? $item['contact_name'] : '',
                    'contact_phone' => !empty($item['phone']) ? $item['phone'] : '',
                    'contact_email' => !empty($item['email']) ? $item['email'] : '',
                    'bill_status' => 'B',
                    'pay_id' => $this->admin->user_id,
                    'pay_date' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $this->admin->user_id,
                ];
                !empty($payId) ? ProjectPay::where('id', $payId)->update($entryData) : ProjectPay::insertGetId($entryData, 'bid_bill_id');
            }
        }
        return true;
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
        $project = (new Project)->getTable();
        $supplier = (new ProjectSupplier)->getTable();
        $pay = (new ProjectPay)->getTable();
        $query = ProjectSupplier::from($supplier . ' as s')
                ->leftJoin($pay . ' AS ps', function ($join) {
                    $join->on('s.project_id', '=', 'ps.project_id')
                    ->on('s.supplier_id', '=', 'ps.supplier_id');
                })
                ->join($project . ' as  bb', function($join) {
                    $join->on('bb.id', '=', 's.project_id');
                })
                ->selectRaw('ps.id,bb.id AS project_id,bb.org_id,bb.`name`,bb.bill_no as project_no,s.supplier_id,'
                . 'ps.bill_status AS pay_status,ps.remark,'
                . 'ps.sure_amount,ps.real_amount,ps.pay_date,ps.id,'
                . 'ps.return_id,ps.return_date,ps.return_status,ps.return_certificate,ps.return_certificate_name,'
                . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name');
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
