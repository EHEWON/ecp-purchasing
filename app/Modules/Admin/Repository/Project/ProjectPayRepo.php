<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Project\Project,
    Project\ProjectPay,
    Project\ProjectSub,
    Project\ProjectSupplier,
    Purchaser,
    User,
    UserSupplier,
    Message,
    MessageReceiver
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    SupplierBaseRepo,
    UserRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectPayRepo extends Repository {

    protected $model;
    protected $sorts = [
    ];

    public function __construct() {
        $this->model = new ProjectPay();
        parent::__construct($this->model);
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
        // $query->whereNotIn('s.entry_status', ['C', 'K', 'T', 'WCY', 'Y','A' ,'C']);
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
        if (!empty($request->bill_status)) {
            $billStatus = $request->bill_status;
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
        if (!empty($request->name)) {
            $query->where('bb.name', 'like', '%' . trim($request->name) . '%');
        }
        if (!empty($request->biz_type)) {
            $query->where('bb.biz_type', trim($request->biz_type));
        }
        if (!empty($request->pay_type)) {
            $query->where('bb.type', trim($request->type));
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

    public function getList(Request $request) {
        $project = (new Project)->getTable();
        $supplier = (new ProjectSupplier)->getTable();
        $pay = (new ProjectPay)->getTable();
        $query = ProjectPay::from($pay . ' as ps')
                ->leftJoin($supplier . ' AS s', function ($join) {
                    $join->on('s.project_id', '=', 'ps.project_id')
                    ->on('s.supplier_id', '=', 'ps.supplier_id');
                })
                ->join($project . ' as  bb', function($join) {
                    $join->on('bb.id', '=', 's.project_id');
                })
                ->selectRaw('ps.id,bb.org_id,ps.project_id,ps.bill_no,bb.name,bb.bill_status as project_status,bb.current_step,bb.bill_no as project_no,s.supplier_id,'
                . 'ps.bill_status AS pay_status,ps.remark,'
                . 'ps.sure_amount,ps.real_amount,ps.pay_date,ps.pay_id,'
                . 'ps.return_id,ps.return_date,ps.return_status,ps.return_certificate,ps.return_certificate_name,'
                . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name,ps.type,bb.bid_open_deadline');
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
        foreach ($data as &$item) {
            $item['sure_amount'] = number_format($item['sure_amount'], 2, '.', '');
            $item['real_amount'] = number_format($item['real_amount'], 2, '.', '');
            $item['pay_status_name'] = $this->getPayStatusText($item['pay_status']);
            $item['return_status_name'] = $this->getReturnStatusText($item['return_status']);
            // $item['entry_status_name'] = $this->getEntryStatusText($item['entry_status']);
            $item['type_name'] = $this->getPayTypeText($item['type']);
            if ($item['return_status'] != 'Y' && ($item['project_status'] == 'X' || $item['project_status'] == 'F' || $item['current_step'] == 'K')) {
                $item['return_status'] = 'N';
            } else {
                $item['return_status'] = 'WCY';
            }
        }
        (new SupplierBaseRepo)->setSuppliers($data);
        (new UserRepo)->setUsers($data, 'pay_id', 'pay_name');
        (new UserRepo)->setUsers($data, 'return_id', 'return_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function getListByProject(int $projectId, $request = []) {
        if (empty($projectId)) {
            return [];
        }
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
                ->selectRaw('ps.id as pay_id,bb.`name`,bb.bill_status as project_status,bb.current_step,bb.bill_no as project_no,ps.project_id,s.supplier_id,ps.bill_status,'
                . 'ps.sure_amount,ps.real_amount,ps.pay_date,ps.remark,ps.return_certificate,ps.return_status,ps.return_certificate_name,'
                . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name,bb.bid_open_deadline');
        $query->where('s.project_id', $projectId);
        //退款与缴费列表都用到此接口不能直接加状态，需要前端传状态
        //$query->whereIn('entry_status', ['L', 'WQR']);
        if (!empty($request)) {
            $this->getWhere($query, $request);
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
            if ($item['return_status'] != 'Y' && ($item['project_status'] == 'X' || $item['project_status'] == 'F' || $item['current_step'] == 'K')) {
                $item['return_status'] = 'N';
            } else {
                $item['return_status'] = 'WCY';
            }
        }
        return $data;
    }

    public function info(int $id) {
        if (empty($id)) {
            return [];
        }
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
                ->selectRaw('ps.id as pay_id,bb.`name`,bb.bill_status as project_status,bb.current_step,bb.bill_no as project_no,ps.project_id,s.supplier_id,ps.bill_status,'
                . 'ps.sure_amount,ps.real_amount,ps.pay_date,ps.remark,ps.return_certificate,ps.return_status,ps.return_certificate_name,'
                . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name');
        $query->where('ps.id', $id);

        $object = $query->orderBy('s.id', 'ASC')
                ->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new SupplierBaseRepo)->setSupplier($data, 'supplier_id', 'supplier_name');
        $data['sure_amount'] = number_format($data['sure_amount'], 2, '.', '');
        $data['real_amount'] = number_format($data['real_amount'], 2, '.', '');
        if ($data['return_status'] != 'Y' && ($data['project_status'] == 'X' || $data['project_status'] == 'F' || $data['current_step'] == 'K')) {
            $data['return_status'] = 'WCY';
        }
        return $data;
    }

    public function audit(int $id, Request $request) {
        $pay = ProjectPay::where('id', $id)->first();
        if (empty($pay)) {
            check(false, '付款信息不存在');
        }
        if (empty($pay->toArray())) {
            check(false, '付款信息不存在');
        }
        $project = Project::where('id', $pay->project_id)->first();
        if (empty($project)) {
            check(false, '招标项目不存在');
        }
        $projectSubObj = ProjectSub::where('project_id', $pay->project_id)
                ->selectRaw('tender_fee,deposit,is_deposit,deposit_stage')
                ->first();
        check(!empty($projectSubObj), '招标项目不存在');
        $sub = $projectSubObj->toArray();
        if (empty($project->toArray())) {
            check(false, '付款信息不存在');
        }
        if (empty($request->pay_status)) {
            check(false, '付款状态不能为空');
        }
        $admin = Auth::guard('admin')->user();
        $flag = ProjectPay::where('id', $id)->update([
            'bill_status' => $request->pay_status,
            'audited_by' => $admin->user_id,
            'audited_at' => date('Y-m-d H:i:s'),
        ]);
        if ($request->pay_status == 'C') {
            $other_pay = ProjectPay::where('supplier_id', $pay->supplier_id)
                    ->where('project_id', $pay->project_id)
                    ->whereNot('type', $pay->type)
                    ->first();
            if (!empty($other_pay)) {
                if ($other_pay['bill_status'] == 'C' || $other_pay['sure_amount'] == 0) {
                    $pay_flag = 'Y';
                }
            } else {
                $pay_flag = 'Y';
            }
            if (!empty($pay_flag) && $pay_flag == 'Y') {
                $flag = ProjectSupplier::where('project_id', $pay->project_id)
                        ->where('supplier_id', $pay->supplier_id)
                        ->update(['pay_flag' => $pay_flag]);
                $orgName = Purchaser::where('id', $project->org_id)->value('name');
                app(Dispatcher::class)->dispatch
                        (new SendMailJob([
                    'projectId' => $pay->project_id,
                    'supplierId' => $pay->supplier_id,
                    'name' => $project->name,
                    'orgName' => $orgName
                        ], 'PROJECT_PAYAUDIT'));
                $this->sendPayMessage($pay->project_id, $pay->supplier_id, $project->name, $orgName, $project->org_id);
            }
        }
        return $flag;
    }

    public function sendPayMessage($projectId, $supplierId, $name, $orgId, $orgName) {

        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/supplierTenders/bidDetails?id=' . $projectId,
                    'sender_id' => $orgId,
                    'message_type' => 'SYSTEM',
                    'message_title' => '您参与的招标项目【' . $name . '】的缴费已确认',
                    'message' => '您好，您参与的招标项目【' . $name . '】的缴费已确认，请尽快登录系统完成标书下载和投标。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function ($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->where('us.supplier_id', $supplierId)
                ->selectRaw('u.user_id,us.supplier_id')
                ->groupBy('us.supplier_id')
                ->groupBy('us.user_id')
                ->get();
        if (empty($userObj)) {
            return;
        }
        $dataList = [];
        $userList = $userObj->toArray();
        foreach ($userList as $user) {
            $dataList[] = [
                'message_id' => $messageId,
                'receiver_id' => $user['user_id'],
                'supplier_id' => $user['supplier_id'],
                'org_id' => $orgId,
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            MessageReceiver::insert($dataList);
        }
    }

    public function returnAudit(int $id, Request $request) {
        $pay = ProjectPay::where('id', $id)->first();
        if (empty($pay)) {
            check(false, '付款信息不存在');
        }
        if ($pay->type != 'EARNEST') {
            check(false, '非保证金收款');
        }
        $project = Project::where('id', $pay->project_id)->first();
        if (empty($project)) {
            check(false, '招标项目不存在');
        }
        if (empty($project->toArray())) {
            check(false, '退款信息不存在');
        }
        if ($project['bill_status'] == 'X' || $project['bill_status'] == 'F' || $project['current_step'] == 'K') {
            $admin = Auth::guard('admin')->user();
            $flag = ProjectPay::where('id', $id)->update([
                'return_status' => 'F',
                'bill_status' => 'F',
                'return_id' => $admin->user_id,
                'return_date' => date('Y-m-d H:i:s'),
                'return_certificate' => $request->return_certificate,
                'return_certificate_name' => $request->return_certificate_name,
                'audited_at' => date('Y-m-d H:i:s'),
            ]);
            return $flag;
        } else {
            check(false, '定标后可退款');
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
            case 'F':
                return '已退费';
        }
    }

    public function getReturnStatusText($status) {
        switch (strtoupper($status)) {
            case 'E':
                return '退费中';
            case 'F':
                return '已退费';
            case 'N':
                return '未退费';
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

    public function getPayTypeText($status) {
        switch (strtoupper($status)) {
            case 'EARNEST':
                return '保证金';
            case 'DOCUMENT':
                return '标书费';
        }
    }

}
