<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Message,
    MessageReceiver,
    Project\Attach,
    Project\Project,
    Project\ProjectInvitation,
    Project\ProjectInvitationEntry,
    Project\ProjectPay,
    Project\ProjectSub,
    Project\ProjectSupplier,
    Project\ProjectSupplierStatistic,
    Purchaser,
    Supplier,
    User,
    UserSupplier
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    Project\ProjectSupplierStatisticRepo,
    SupplierBaseRepo,
    SupplierContactRepo,
    BidModeRepo,
    PurTypeRepo,
    UserRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectSupplierRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectSupplier();
        parent::__construct($this->model);
    }

    public function suppliers(Request $request) {
        $supplierTable = (new Supplier)->getTable();
        $supplierStatisticTable = (new ProjectSupplierStatistic)->getTable();
        $query = Supplier::from($supplierTable . ' AS s')
                ->leftJoin($supplierStatisticTable . ' as ss', function($join) {
                    $join->on('s.id', '=', 'ss.supplier_id')
                    ->where('ss.org_id', '=', 's.purchaser_id');
                })
                ->selectRaw('s.id,s.purchaser_id,s.name,s.supplier_no,s.enable,'
                        . 's.status,s.purchaser_id,s.source,ss.nomination_qty,ss.won_qty')
                ->where('s.deleted_flag', 'N');
        $curId = $this->getPPurchaserId();
        $query->where('s.enable', 1);
        $query->where('s.status', 'APPROVED');
        $query->where('s.deleted_flag', 'N');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('s.name', 'like', '%' . $keyword . '%')
                        ->orWhere('s.supplier_no', 'like', '%' . $keyword . '%');
            });
        }

        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $query->orderBy('s.created_at', 'DESC');
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        $supplierRepo = new SupplierBaseRepo();
        foreach ($data as &$item) {
            $item['source_name'] = $supplierRepo->getSourceText($item['source']);
            $item['status_name'] = $supplierRepo->getStatusText($item['status']);
            $item['enable_name'] = $supplierRepo->getEnableText($item['enable']);
        }
        (new SupplierContactRepo)->setDefaultContacts($data, 'id');
        (new ProjectSupplierStatisticRepo)->setSuppliers($data, 'supplier_id', $curId);
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }
    public function getList(int $projectId, $supplier_id = '', $shortlistFlag = null, $orgId = null) {
        if (empty($projectId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId);
        if (!empty($shortlistFlag)) {
            $qurey->where('shortlist_flag', $shortlistFlag);
        }
        if ($supplier_id) {
            $qurey->where('supplier_id', $supplier_id);
        }
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $project = Project::where('id', $projectId)->first();
        $currentStep = $project['current_step'];
        $currentSteps = explode(',', $currentStep);
        foreach ($data as &$item) {
            $item['invitation_status_name'] = $this->getInvitationStatusText($item['invitation_status']);
            $item['status_name'] = $this->getBillStatusText($item['status']);
            $item['pay_flag_name'] = $this->getPayFlay($item['pay_flag']);
            if ($item['is_tender'] == 1) {
                $item['tender_name'] = '已投标';
            } elseif ($item['enroll_status'] === 'WCY' || $item['enroll_status'] === 'N') {
                $item['tender_name'] = '';
            } elseif (in_array('B', $currentSteps) || in_array('C', $currentSteps) || in_array('F', $currentSteps)) {
                $item['tender_name'] = '待投标';
            } else {
                $item['tender_name'] = '弃标';
            }
        }
        (new ProjectSupplierStatisticRepo)->setSuppliers($data, 'supplier_id', $orgId);
        return $data;
    }

    public function updateData(int $projectId, Request $request) {
        $project = Project::where('id', $projectId)->first();
        if (empty($project)) {
            check(false, '招标项目不存在');
        }
        ProjectSupplier::where('project_id', $projectId)->delete();
        if (!empty($request->base) && !empty($request->base['bid_mode_id']) && $request->base['bid_mode_id'] !== '2') {
            return[];
        }
        $supplierList = $request->supplier;
        if (empty($supplierList)) {
            return [];
        }
        (new ProjectSupplierStatisticRepo)->setSuppliers($supplierList, 'supplier_id', $project->org_id);
        $dataList = [];
        foreach ($supplierList as $key => $supplier) {
            if (empty($supplier['supplier_id'])) {
                continue;
            }

            $dataList[] = [
                'project_id' => $projectId,
                'seq' => $key + 1,
                'supplier_id' => !empty($supplier['supplier_id']) ? $supplier['supplier_id'] : null,
                'supplier_contact' => !empty($supplier['supplier_contact']) ? $supplier['supplier_contact'] : null,
                'contact_phone' => !empty($supplier['contact_phone']) ? $supplier['contact_phone'] : null,
                'contact_email' => !empty($supplier['contact_email']) ? $supplier['contact_email'] : null,
                'supplier_statistic' => !empty($supplier['supplier_statistic']) ? $supplier['supplier_statistic'] : null,
                'winning_num' => !empty($supplier['winning_num']) ? $supplier['winning_num'] : 0,
                'nomination_num' => !empty($supplier['nomination_num']) ? $supplier['nomination_num'] : 0,
                'supplier_comment' => !empty($supplier['supplier_comment']) ? $supplier['supplier_comment'] : null,
                'supplier_name' => !empty($supplier['supplier_name']) ? $supplier['supplier_name'] : null,
                'invitation_status' => !empty($supplier['invitation_status']) ? $supplier['invitation_status'] : null,
                'supplier_deposit' => !empty($supplier['supplier_deposit']) ? $supplier['supplier_deposit'] : null,
                'tender_fee' => !empty($supplier['tender_fee']) ? $supplier['tender_fee'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        return ProjectSupplier::insert($dataList);
    }

//增补供应商
    public function addData(int $projectId, Request $request) {
        $info = ProjectSupplier::where('project_id', $projectId)->orderBy('seq', 'desc')->first();
        $projectObj = Project::where('id', $projectId)->first();
        if (empty($projectObj)) {
            check(false, '招标项目不存在');
        }
        $project = $projectObj->toArray();
        (new UserRepo())->setUser($project, 'contact_id', 'contact_name');
        $supplierList = $request->supplier;
        if (empty($supplierList)) {
            check(false, '请选择需要增补入围的供应商');
        }
        (new ProjectSupplierStatisticRepo)->setSuppliers($supplierList, 'supplier_id', $projectObj->org_id);
        $dataList = [];
        foreach ($supplierList as $key => $supplier) {
            $infocheck = ProjectSupplier::where('project_id', $projectId)->where('supplier_id', $supplier['supplier_id'])->first();
            if (!empty($infocheck)) {
                continue;
            }
            $supplierIds[] = $supplier['supplier_id'];
            $dataList[] = [
                'project_id' => $projectId,
                'seq' => !empty($info['seq']) ? $info['seq'] + $key + 1 : $key + 1,
                'supplier_id' => !empty($supplier['supplier_id']) ? $supplier['supplier_id'] : null,
                'supplier_contact' => !empty($supplier['supplier_contact']) ? $supplier['supplier_contact'] : null,
                'contact_phone' => !empty($supplier['contact_phone']) ? $supplier['contact_phone'] : null,
                'contact_email' => !empty($supplier['contact_email']) ? $supplier['contact_email'] : null,
                'supplier_statistic' => !empty($supplier['supplier_statistic']) ? $supplier['supplier_statistic'] : null,
                'winning_num' => !empty($supplier['winning_num']) ? $supplier['winning_num'] : 0,
                'nomination_num' => !empty($supplier['nomination_num']) ? $supplier['nomination_num'] : 0,
                'supplier_comment' => !empty($supplier['supplier_comment']) ? $supplier['supplier_comment'] : null,
                'supplier_name' => !empty($supplier['supplier_name']) ? $supplier['supplier_name'] : null,
                'invitation_status' => !empty($supplier['invitation_status']) ? $supplier['invitation_status'] : null,
                'supplier_deposit' => !empty($supplier['supplier_deposit']) ? $supplier['supplier_deposit'] : null,
                'tender_fee' => !empty($supplier['tender_fee']) ? $supplier['tender_fee'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        if (empty($dataList)) {
            check(false, '请选择的增补入围供应商已被邀请,请勿重复邀请');
        }
        $res = ProjectSupplier::insert($dataList);
        $this->sendsSupplier($project, $supplierIds);
        return $res;
    }

    public function sendsSupplier($ProjectData, $supplierIds) {
        $orgName = Purchaser::where('id', $ProjectData['org_id'])
                ->value('name');
        if ($ProjectData['bid_mode_id'] == 1) {
            return true;
        }
        if (empty($supplierIds)) {
            return [];
        }
        $this->projectInvitation($ProjectData, $supplierIds);
        $this->sendSupplierMail($ProjectData, $supplierIds, $orgName);
        $this->sendSupplierMessage($ProjectData, $ProjectData['id'], $supplierIds, $orgName);
    }

    public function sendSupplierMessage($ProjectData, $projectId, $supplierIds, $orgName) {

        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/supplierTenders/entryDetails?id=' . $projectId,
                    'sender_id' => $ProjectData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . env('APP_NAME') . '】发布【' . $ProjectData['name'] . '】的招标邀请',
                    'message' => '您好，发布【' . $ProjectData['name'] . '】的邀请招标，请尽快登录系统完成投标报名。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function ($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->whereIn('us.supplier_id', $supplierIds)
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
                'org_id' => $ProjectData['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            MessageReceiver::insert($dataList);
        }
    }

    public function sendSupplierMail($ProjectData, $supplierIds, $orgName) {
        /* new SendMailJob([
          'projectData' => $ProjectData,
          'supplierIds' => $supplierIds,
          'orgName' => $orgName,
          'type' => '1',
          ], 'PROJECT'); */
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'projectData' => $ProjectData,
            'supplierIds' => $supplierIds,
            'orgName' => $orgName,
            'type' => '1',
                ], 'PROJECT'));
    }

    public function projectInvitation($projectData, $supplierIds) {
        if ($projectData['bid_mode_id'] != 2) {
            return;
        }
        (new PurTypeRepo)->setPurType($projectData, 'pur_type_id', 'pur_type_name');
        $invitationId = ProjectInvitation::where('project_id', $projectData['id'])->value('id');
        $supplierObj = Supplier::whereIn('id', $supplierIds)
                ->where('deleted_flag', 'N')
                ->where('status', 'APPROVED')
                ->where('enable', '1')
                ->get();
        $pSupplierObj = ProjectSupplier::whereIn('supplier_id', $supplierIds)
                ->where('project_id', $projectData['id'])
                ->selectRaw('supplier_id,supplier_deposit')
                ->get();
        if (empty($supplierObj)) {
            return;
        }
        $supplierList = $supplierObj->toArray();
        $pSupplierList = !empty($pSupplierObj) ? $pSupplierObj->toArray() : [];
        $pSupplierArr = array_column($pSupplierList, 'supplier_deposit', 'supplier_id');
        $subObj = ProjectSub::where('project_id', $projectData['id'])
                ->selectRaw('deposit,tender_fee')
                ->first();
        $projectData['deposit'] = !empty($subObj) ? $subObj->deposit : null;
        $projectData['tender_fee'] = !empty($subObj) ? $subObj->tender_fee : null;
        foreach ($supplierList AS $supplier) {
            $newData = $projectData;
            $newData['supplier_name'] = $supplier['name'];
            $newData['supplier_deposit'] = !empty($pSupplierArr[$supplier['id']]) ? $pSupplierArr[$supplier['id']] : null;
            $newContent = view('tpl.project_invitation', $newData)->toHtml();
            $invitationDataList[] = [
                'invitation_id' => $invitationId,
                'content' => $newContent,
                'invitation_status' => 'C',
                'supplier_id' => $supplier['id'],
                'invitation_user' => $projectData['contact_id'],
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ];
            ProjectSupplier::where('supplier_id', $supplier['id'])
                    ->where('project_id', $projectData['id'])
                    ->update([
                        'invitation_status' => 'C'
            ]);
        }
        ProjectInvitationEntry::insert($invitationDataList);
    }

//供方入围
    public function shortlist(int $projectId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $supplierList = $request->data;
        $project = Project::where('id', $projectId)->first();
        $projectSub = ProjectSub::where('project_id', $projectId)->first();
//        if ($projectSub['invitation_deadline'] > date('Y-m-d H:i:s')) {
//            check(false, '未到报名截止时间');
//        }
        ProjectSupplier::where('project_id', $projectId)->update(['shortlist_flag' => 'N']);
        foreach ($supplierList as $supplier) {
            if (!empty($supplier['supplier_id'])) {
                $dataList['shortlist_flag'] = $supplier['shortlist_flag'];
                if ($request->status === 'C') {
                    $dataList['shortlist_at'] = date('Y-m-d H:i:s');
                    $dataList['status'] = 'E';
                }
                $dataList['shortlist_describe'] = $supplier['shortlist_describe'];
                $dataList['supplier_comment'] = $supplier['shortlist_describe'];
                ProjectSupplier::where('project_id', $projectId)->where('supplier_id', $supplier['supplier_id'])->where('enroll_status', 'Y')->update($dataList);
            }
            if ($request->status === 'C' && $supplier['shortlist_flag'] == 'Y') {
                $payEarnestInfo = ProjectPay::where('project_id', $projectId)->where('type', 'EARNEST')->where('supplier_id', $supplier['supplier_id'])->first();
                if ($projectSub['is_supplier_get'] != 'Y') {
                    $deposit = $projectSub->deposit;
                } else {
                    $projectSupplierInfo = ProjectSupplier::where('project_id', $projectId)->where('supplier_id', $supplier['supplier_id'])->first();
                    $deposit = $projectSupplierInfo['supplier_deposit'];
                }
                if (empty($payEarnestInfo) && $deposit != 0) {
                    $entryData = [
                        'project_id' => $project['id'],
                        'project_no' => $project['bill_no'],
                        'project_name' => $project['name'],
                        'org_id' => $project['org_id'],
                        'supplier_id' => $supplier['supplier_id'],
                        'type' => 'EARNEST',
                        'sure_amount' => $deposit,
                        'bill_status' => 'A',
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => $admin->user_id,
                    ];
                    ProjectPay::insertGetId($entryData);
                }
                $payDocInfo = ProjectPay::where('project_id', $projectId)->where('type', 'DOCUMENT')->where('supplier_id', $supplier['supplier_id'])->first();
                if (empty($payDocInfo) && $projectSub->tender_fee != 0) {
                    $entryData = [
                        'project_id' => $project['id'],
                        'project_no' => $project['bill_no'],
                        'project_name' => $project['name'],
                        'org_id' => $project['org_id'],
                        'supplier_id' => $supplier['supplier_id'],
                        'type' => 'DOCUMENT',
                        'sure_amount' => $projectSub->tender_fee,
                        'bill_status' => 'A',
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => $admin->user_id,
                    ];
                    ProjectPay::insertGetId($entryData);
                }
                if ($projectSub->tender_fee == 0 && $projectSub->deposit == 0) {
                    ProjectSupplier::where('project_id', $projectId)->where('supplier_id', $supplier['supplier_id'])->update(['pay_flag' => 'Y']);
                } elseif ($projectSub->tender_fee == 0 && $projectSub->deposit != 0) {
                    ProjectSupplier::where('project_id', $projectId)->where('supplier_id', $supplier['supplier_id'])->update(['pay_flag' => 'DOCUMENT']);
                } elseif ($projectSub->tender_fee != 0 && $projectSub->deposit == 0) {
                    ProjectSupplier::where('project_id', $projectId)->where('supplier_id', $supplier['supplier_id'])->update(['pay_flag' => 'EARNEST']);
                }
                /*
                  $enroll_id =ProjectSupplier::where('project_id', $projectId)->where('supplier_id', $supplier['supplier_id'])->vlue('enroll_id');
                  if(!empty($enroll_id)){
                  $tihs->sendMessage($projectId, $enroll_id, $supplier['shortlist_flag']);
                  } */
            }
        }
        Attach::where('project_id', $projectId)->whereIn('group', ['INVITATION'])->delete();
        if (!empty($request->attachs)) {
            foreach ($request->attachs as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'group' => 'INVITATION',
                    'project_id' => $projectId,
                    'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                    'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                    'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
            if (!empty($attachList)) {
                Attach::insert($attachList);
            }
        }

        if (!empty($request->investigate)) {
            ProjectSub::where('project_id', $projectId)->update(['investigate' => $request->investigate]);
        }
//        if ($request->status === 'C') {
//            $flag = Project::where('id', $projectId)
//                    ->update(['shortlist_at' => date('Y-m-d H:i:s')]);
//            $bidDocument = Project::where('id', $projectId)->value('bid_document');
//            //未入围
//            ProjectSupplier::where('project_id', $projectId)->where('enroll_status', 'Y')->where('shortlist_flag', 'N')->update(['status' => 'J']);
//            $data = [
//                'supplier_invitation' => '1',
//                'shortlist_at' => date('Y-m-d H:i:s'),
//                'updated_by' => $admin->user_id,
//                'updated_at' => date('Y-m-d H:i:s'),
//            ];
//            if ($bidDocument == '1') {
//                $data['current_step'] = 'F';
//            } else {
//                $data['current_step'] = 'C';
//            }
//            Project::where('id', $projectId)->update($data);
//            (new ProjectPublishRepo)->init($projectId);
//            $this->sends($projectId);
//            return $flag;
//        }
        return true;
    }

    public function shortSend() {
        
    }

    public function getInvitationStatusText($status) {
        switch (strtoupper($status)) {
            case 'C':
                return '已发送';
            case 'Y':
                return '已接受';
            case 'N':
                return '已拒绝';
            default :
                return '未发送';
        }
    }

    public function getPayFlay($status) {
        switch (strtoupper($status)) {
            case 'N':
                return '已发送';
            case 'EARNEST':
                return '保证金';
            case 'DOCUMENT':
                return '标书费';
            case 'Y':
                return '缴费完成';
        }
    }

    public function getEnrollStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '暂存';
            case 'B':
                return '已提交';
            case 'C':
                return '已审核';
            case 'I':
                return '审核中';
            case 'X':
                return '已流标';
            case 'F':
                return '已作废';
        }
    }

    /*
     * 当前状态F:已中标G:未中标H:报名截止K:保证金未收L:待缴费N:未报名T:待报名WCY:未参与Y:已报名
     */

    public function getBillStatusText($status) {
        switch (strtoupper($status)) {
            case 'X':
                return '已流标';
            case 'N':
                return '不报名';
            case 'Y':
                return '已报名';
            case 'WCY':
                return '未参与';
            case 'T':
                return '待报名';
            case 'E':
                return '已入围';
            case 'J':
                return '未入围';
            case 'K':
                return '待投标';
            case 'I':
                return '已投标';
            case 'H':
                return '弃标';
            case 'F':
                return '中标';
            case 'G':
                return '未中标';
        }
    }

    public function sends($projectId) {
        $ProjectObj = Project::where('id', $projectId)->first();
        if (empty($ProjectObj)) {
            return;
        }
        $ProjectData = $ProjectObj->toArray();
        $orgName = Purchaser::where('id', $ProjectData['org_id'])
                ->value('name');
        $supplierIds = ProjectSupplier::where('project_id', $projectId)
                ->where('deleted_flag', 'N')
                ->where('shortlist_flag', 'Y')
                ->pluck('supplier_id');
        if (empty($supplierIds)) {
            return [];
        }
        $this->sendMail($ProjectData, $supplierIds, $orgName);
        $this->sendMessage($ProjectData, $projectId, $supplierIds, $orgName);
    }

    public function sendMessage($ProjectData, $projectId, $supplierIds, $orgName) {

        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/supplierTenders/entryDetails?id=' . $projectId,
                    'sender_id' => $ProjectData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . env('APP_NAME') . '】恭喜你！成功入围发布【' . $ProjectData['name'] . '】【' . env('APP_NAME') . '】的招标项目。',
                    'message' => '您好，发布【' . $ProjectData['name'] . '】的招标项目，您已成功入围，请做好投标准备。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function ($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->whereIn('us.supplier_id', $supplierIds)
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
                'org_id' => $ProjectData['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            MessageReceiver::insert($dataList);
        }
    }

    public function sendMail($ProjectData, $supplierIds, $orgName) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'projectData' => $ProjectData,
            'supplierIds' => $supplierIds,
            'orgName' => $orgName
                ], 'PROJECTSELECTED'));
    }

    public function supplierList(Request $request) {
        $filed = 'p.id,p.bill_no,p.name,p.org_id,ps.nomination_num,ps.shortlist_at,'
                . 'ps.winning_num,ps.winning_at,ps.winning_amount,p.bid_mode_id,'
                . 'p.created_at,p.pur_type_id,p.created_by';
        if (empty($request->supplier_id)) {
            check(false, '请选择供应商');
        }
        if ((empty($request->winning_flag) || $request->winning_flag != 'Y') && (empty($request->nomination_flag) || $request->nomination_flag !== 'Y')) {
            check(false, '入围标志和中标标志不能都为空');
        }
        $project = new Project();
        $query = $project
                ->from($project->getTable() . ' AS p')
                ->join($this->model->getTable() . ' AS ps', function($join) {
                    $join->on('p.id', '=', 'ps.project_id');
                })
                ->selectRaw($filed);
        if ($request->keyword) {
            $query->where('p.name', 'like', '%' . trim($request->keyword) . '%');
        }
        if ($request->nomination_flag && $request->nomination_flag == 'Y') {
            $query->where('ps.shortlist_flag', 'Y');
        }
        if ($request->winning_flag && $request->winning_flag == 'Y') {
            $query->where('ps.status', 'F');
        }
//        if ($request->org_id) {
//            $query->where('ps.org_id', $request->org_id);
//        }
        $purchaserId = $this->getPPurchaserId();
        $query->where('p.org_id', $purchaserId);
        $query->whereIn('p.bill_status', ['C', 'X']);
        $query->where('ps.supplier_id', $request->supplier_id);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $query->orderBy('p.created_at', 'DESC');
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'creator_name');
        (new BidModeRepo)->setBidModes($data, 'bid_mode_id', 'bid_mode_name');
        (new PurTypeRepo)->setPurTypes($data, 'pur_type_id', 'pur_type_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

}
