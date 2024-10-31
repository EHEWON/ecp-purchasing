<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Message,
    MessageReceiver,
    Project\Attach,
    Project\Project,
    Project\ProjectDecision,
    Project\ProjectDecisionEntry,
    Project\ProjectDecisionSupplier,
    Project\ProjectMember,
    Project\ProjectSub,
    Project\ProjectSupplier,
    Project\ProjectThanks,
    Project\ProjectThanksEntry,
    Supplier,
    User,
    UserSupplier
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    BidModeRepo,
    NoticeManageRepo,
    Project\ProjectDecisionEntryRepo,
    Project\ProjectDecisionFileRepo,
    Project\ProjectRepo,
    SupplierBaseRepo,
    UserRepo,
    OrgRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB
};

class ProjectDecisionRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectDecision();
        parent::__construct($this->model);
    }

    protected function getOrder(&$query) {
        /**
         * 排序
         */
        $query->orderBy('pd.decision_at', 'DESC');
        $query->orderBy('p.setup_date', 'DESC');
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request) {
        $project = (new Project)->getTable();
        $decision = (new ProjectDecision)->getTable();
        $sub = (new ProjectSub)->getTable();
        $query = Project::from($project . ' as p')
                ->leftJoin($decision . ' as pd', function($join) {
                    $join->on('pd.project_id', '=', 'p.id');
                })
                ->join($sub . ' as ps', function($join) {
                    $join->on('ps.project_id', '=', 'p.id');
                })
                ->selectRaw('p.id,p.name,p.bill_no,p.bill_status,p.current_step,'
                        . 'pd.decision_status,p.bid_mode_id,p.org_id,'
                . 'p.bid_decision_date,p.bid_publish_date,pd.decision_status,'
                        . 'ps.total_control,p.bid_decision,ps.charging_stage');

        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        $projectRepo = new ProjectRepo;
        foreach ($data as &$item) {
            $item['total_control'] = number_format($item['total_control'], 2, '.', ',');
            $item['bill_status_name'] = $projectRepo->getBillStatusText($item['bill_status']);
            $item['decision_status_name'] = $this->getDecisionStatusText($item['decision_status']);
            $item['current_step_name'] = $projectRepo->getCurrentStepText($item['current_step']);
        }
        (new OrgRepo)->setOrgs($data, 'org_id', 'org_name');
        (new ProjectMemberRepo)->setRespBusinesies($data, 'id');
        (new BidModeRepo)->setBidModes($data, 'bid_mode_id', 'bid_mode_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function decisionTotal() {
        $project = (new Project)->getTable();
        $decision = (new ProjectDecision)->getTable();
        $sub = (new ProjectSub)->getTable();
        $query = Project::from($project . ' as p')
                ->leftJoin($decision . ' as pd', function($join) {
                    $join->on('pd.project_id', '=', 'p.id');
                })
                ->join($sub . ' as ps', function($join) {
                    $join->on('ps.project_id', '=', 'p.id');
                })
                ->whereIn('p.bill_status', ['C', 'X'])
                ->where('p.current_step', 'K')
                ->where(function($q) {
            $q->where('p.bid_decision', '0')
            ->orWhere('p.bid_decision', '')
            ->orWhereNull('p.bid_decision');
        });
        return $query->count();
    }

    public function info(int $projectId) {
        if (empty($projectId)) {
            return [];
        }
        $project = (new Project)->getTable();
        $sub = (new ProjectSub)->getTable();
        $baseObj = Project::from($project . ' as p')
                ->join($sub . ' as ps', function($join) {
                    $join->on('ps.project_id', 'p.id');
                })
                ->where('p.id', $projectId)
                ->selectRaw('p.id,p.name,p.bill_status,p.current_step,'
                        . 'p.commercial_doc_end_date,p.technical_doc_end_date,'
                        . 'p.bid_publish_date,p.bid_mode_id,p.is_separate_doc,'
                        . 'p.bid_document,ps.evaluate_decide_way_id,ps.doc_type,p.org_id'
                        . ',p.bid_decision_date,ps.total_control')
                ->first();
        if (empty($baseObj)) {
            check(false, '招标不存在');
        }
        $admin = Auth::guard('admin')->user();
        $base = $baseObj->toArray();
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId);
        $object = $qurey->orderBy('id', 'ASC')->first();
        $projectRepo = new ProjectRepo();
        $base['doc_type_name'] = $projectRepo->getDocTypeText($base['doc_type']);
        $base['current_step_name'] = $projectRepo->getCurrentStepText($base['current_step']);
        $base['total_control'] = number_format($base['total_control'], 2, '.', ',');
        (new BidModeRepo)->setBidMode($base, 'bid_mode_id', 'bid_mode_name');
        $base['evaluate_decide_name'] = $projectRepo->getEvaluateDecide($base['evaluate_decide_way_id']);
        (new ProjectMemberRepo)->setRespBusiness($base, 'id');
        (new OrgRepo)->setOrg($base, 'org_id', 'org_name');
        $data = ['base' => $base, 'decision' => [
        ]];
        $decision = !empty($object) ? $object->toArray() : [
            'decision_status' => null,
            'decision_at' => null,
            'created_by' => $admin->user_id,
        ];
        $files = (new ProjectDecisionFileRepo)->getList($projectId);
        $decision['decision_status_name'] = $this->getDecisionStatusText($decision['decision_status']);
        $data['eva_report'] = !empty($files['eva_report']) ? $files['eva_report'] : [];
        $data['win_report'] = !empty($files['win_report']) ? $files['win_report'] : [];
        $data['attachs'] = !empty($files['attachs']) ? $files['attachs'] : [];
        (new UserRepo)->setUser($decision, 'created_by', 'created_name');

//        $data['entry'] = (new ProjectDecisionEntryRepo)->getList($projectId);
        $supplierList = (new ProjectDecisionSupplierRepo)->getList($projectId);

        $incluTaxAmount = 0; //投标报价（含税价）
        $taxAmount = 0; //税额
        $exceptTaxAmount = 0; //投标报价（不含税价）
        foreach ($supplierList as &$supplier) {
            if ($supplier['adopt_flag'] === '1') {
                $incluTaxAmount += $supplier['inclu_tax_amount'];
                $taxAmount += $supplier['tax_amount'];
                $exceptTaxAmount += $supplier['inclu_tax_amount'] - $supplier['tax_amount'];
            }

            $supplier['exc_tax_amount'] = number_format(($supplier['inclu_tax_amount'] - $supplier['tax_amount']), 2, '.', ',');

            $supplier['inclu_tax_amount'] = number_format($supplier['inclu_tax_amount'], 2, '.', ',');
            $supplier['tax_amount'] = number_format($supplier['tax_amount'], 2, '.', ',');
        }
        $data['supplier'] = $supplierList;
        $decision['inclu_tax_amount'] = number_format($incluTaxAmount, 2, '.', ',');
        $decision['tax_amount'] = number_format($taxAmount, 2, '.', ',');
        $decision['exc_tax_amount'] = number_format($exceptTaxAmount, 2, '.', ',');


        $data['decision'] = $decision;
        $data['process'] = $projectRepo->getProcess($projectId);
        return $data;
    }

    public function edited(int $projectId, Request $request) {
        $decision = $request->decision;
        $admin = Auth::guard('admin')->user();
        $project = Project::where('id', $projectId)->first();
        check(!empty($project), '招标项目不存在');
        check($project->current_step === 'K', '招标项目当前阶段不是定标');
        $decisionObj = ProjectDecision::where('project_id', $projectId)->first();
        if (!empty($decisionObj)) {
            check(!empty($decisionObj) && $decisionObj->decision_status == 'A', '您已提交定标,请勿重复提交');
        }
        $data = [
            'project_id' => $projectId,
            'bill_no' => $project->bill_no,
            'org_id' => $project->org_id,
            'bill_status' => $project->bill_status,
            'decision_status' => !empty($decision['decision_status']) ? $decision['decision_status'] : 'A',
            'decision_at' => !empty($decision['decision_status']) && $decision['decision_status'] === 'C' ? date('Y-m-d H:i:s') : null,
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'decision_at' => date('Y-m-d H:i:s'),
        ];

        $flag = ProjectDecision::upsert($data, ['project_id'], ['updated_by', 'decision_at', 'updated_at', 'decision_status', 'bill_no', 'org_id', 'bill_status']);
        (new ProjectDecisionFileRepo)->updateData($projectId, $request);
        (new ProjectDecisionSupplierRepo)->updateData($projectId, $request);
        if (!empty($decision['decision_status']) && $decision['decision_status'] === 'C') {
            Project::where('id', $projectId)->update([
                'bid_decision' => '1',
                'updated_by' => $admin->user_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->sends($projectId);
        }
        return $flag;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        $admin = Auth::guard('admin')->user();
        $member = (new ProjectMember)->getTable();
        $project = (new ProjectDecision)->getTable();
        $count = ProjectDecision::whereIn('project_id', $request->ids)
                ->count();
        $countP = ProjectDecision::whereIn('p.project_id', $request->ids)
                ->from($project . ' as p')
                ->whereIn('p.decision_status', ['A'])
                ->whereNot(function ($q)use ($admin, $member) {
                    $q->where('p.created_by', $admin->user_id)
                    ->orWhereRaw('EXISTS(SELECT id FROM ' . $member
                            . ' AS m where m.project_id=p.project_id '
                            . ' AND FIND_IN_SET(\'K\',m.resp_business )'
                            . ' AND m.user_id=' . $admin->user_id . ')');
                })
                ->count();
        DB::beginTransaction();
        $projectIds = ProjectDecision::whereIn('project_id', $ids)
                ->where('decision_status', 'A')
                ->where(function ($q)use ($admin, $member) {
                    $q->where('created_by', $admin->user_id)
                    ->orWhereRaw('EXISTS(SELECT id FROM ' . $member
                            . ' AS m where m.project_id=project_decision.project_id '
                            . ' AND FIND_IN_SET(\'K\',m.resp_business )'
                            . ' AND m.user_id=' . $admin->user_id . ')');
                })
                ->pluck('project_id');
        $flag = ProjectDecision::whereIn('project_id', $ids)
                ->where('decision_status', 'A')
                ->where(function ($q)use ($admin, $member) {
                    $q->where('created_by', $admin->user_id)
                    ->orWhereRaw('EXISTS(SELECT id FROM ' . $member
                            . ' AS m where m.project_id=project_decision.project_id '
                            . ' AND FIND_IN_SET(\'K\',m.resp_business )'
                            . ' AND m.user_id=' . $admin->user_id . ')');
                })
                ->delete();
        if ($projectIds) {
            $projectIdList = $projectIds->toArray();
            $projectObj = ProjectSub::select('project_id', 'evaluate_decide_way_id')
                    ->whereIn('project_id', $projectIdList)
                    ->get();
            $projectList = !empty($projectObj) ? $projectObj->toArray() : [];
            $projectArr = array_column($projectList, 'evaluate_decide_way_id', 'project_id');
            ProjectDecisionSupplier::whereIn('project_id', $projectIds)->delete();
            ProjectDecisionEntry::whereIn('project_id', $projectIds)->delete();
            Attach::whereIn('project_id', $projectIds)
                    ->whereIn('group', ['DECISION', 'EVA_REPORT', 'WIN_REPORT'])
                    ->delete();
            foreach ($projectIdList as $projectId) {
                $decideWay = !empty($projectArr[$projectId]) ? $projectArr[$projectId] : 2;
                (new ProjectDecisionRepo)->init($projectId);
                (new ProjectDecisionEntryRepo)->init($projectId);
                (new ProjectDecisionSupplierRepo)->init($projectId, $decideWay);
            }
        }
        $str = '';
        if (!empty($flag)) {
            $str .= '成功删除' . $flag . '条';
        }
        if (!empty($countP)) {
            $str .= (!empty($str) ? '，' : '') . '不是经办业务人不能删除的定标' . $countP . '条';
        }
        DB::commit();
        check($count === $flag, $str);
        return $flag ? 200 : $flag;
    }

    public function notice($projectId) {
        $projectObj = Project::where('id', $projectId)->first();
        $supplierObj = ProjectDecisionSupplier::where('project_id', $projectId)
                ->where('adopt_flag', 1)
                ->get();
        if (empty($supplierObj)) {
            return;
        }

        $projectData = $projectObj->toArray();
        (new UserRepo)->setUser($projectData, 'contact_id', 'contact_name');
        $supplierList = $supplierObj->toArray();
        (new SupplierBaseRepo)->setSuppliers($supplierList);
        $supplierNames = array_column($supplierList, 'supplier_name');
        $taxAmount = array_sum(array_column($supplierList, 'inclu_tax_amount'));
        $projectData['supplier_name'] = implode(',', $supplierNames);
        $projectData['tax_amount'] = $taxAmount;
        $this->projectThanks($projectId, $projectData);
        $content = view('tpl.project_cfm_notice', $projectData)->toHtml();
        $data = [
            'biz_type' => 5,
            'due_date' => date('Y-m-d H:i:s', strtotime($projectData['enroll_deadline'])),
            'org_id' => $projectData['org_id'],
            'bill_no' => (new NoticeManageRepo)->getNoticeNo(),
            'src_bill_id' => $projectData['id'],
            'src_bill_type' => 'sou_decision',
            'sup_scope' => $projectData['bid_mode_id'],
            'bill_status' => 'C',
            'bill_type_id' => 0,
            'src_bill_no' => '中标通知书：' . $projectData['bill_no'],
            'title' => $projectData['name'] . '中标通知书',
        ];
        $data['content'] = $content;
        return $data;
    }

    public function sends($projectId) {
        $projectObj = Project::where('id', $projectId)->first();
        $supplierObj = ProjectDecisionSupplier::where('project_id', $projectId)
                ->where('adopt_flag', 1)
                ->get();
        if (empty($supplierObj)) {
            return;
        }
        $projectData = $projectObj->toArray();
        $supplierList = $supplierObj->toArray();
        (new SupplierBaseRepo)->setSuppliers($supplierList);
        $supplierNames = array_column($supplierList, 'supplier_name');
        $winSupplierIds = array_column($supplierList, 'supplier_id');
        $projectData['supplier_name'] = implode(',', $supplierNames);
        $data = $this->notice($projectData);
        $nrequest = (new Request);
        $nrequest->merge($data);
        (new NoticeManageRepo)->addData($nrequest);
        $supplierIdObj = ProjectSupplier::where('project_id', $projectId)
                ->where('deleted_flag', 'N')
                ->whereIn('status', ['G', 'X', 'Y', 'E', 'J', 'K', 'I', 'H'])
                ->pluck('supplier_id');
        if (empty($supplierIdObj)) {
            return [];
        }
        $supplierIds = $supplierIdObj->toArray();
//        $this->projectThanks($projectData, $supplierIds);
        $this->sendWinMail($projectData, $winSupplierIds);
        $this->sendFailMail($projectData, $supplierIds);
        $this->sendWinMessage($projectData, $projectId, $winSupplierIds);
        $this->sendFailMessage($projectData, $projectId, $supplierIds);
    }

    public function sendFailMessage($ProjectData, $projectId, $failSupplierIds) {

        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/supplierTenders/bidDetails?id=' . $projectId,
                    'sender_id' => $ProjectData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . $ProjectData['name'] . '】' . '感谢信',
                    'message' => '很遗憾！你参与的【' . $ProjectData['name'] . '】采购招标并未中标，感谢您对我们公司的大力支持',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function ($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->whereIn('us.supplier_id', $failSupplierIds)
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

    public function sendWinMessage($ProjectData, $projectId, $winSupplierIds) {

        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/supplierTenders/bidDetails?id=' . $projectId,
                    'sender_id' => $ProjectData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '招标中标通知',
                    'message' => '恭喜！你参与的【' . $ProjectData['name'] . '】已经中标',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function ($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->whereIn('us.supplier_id', $winSupplierIds)
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

    public function sendWinMail($ProjectData, $supplierIds) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'projectData' => $ProjectData,
            'supplierIds' => $supplierIds,
                ], 'WIN_PROJECT'));
    }

    public function sendFailMail($ProjectData, $supplierIds) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'projectData' => $ProjectData,
            'supplierIds' => $supplierIds,
                ], 'FAIL_PROJECT'));
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    public function getWhere(&$query, Request $request) {
        $query->whereIn('p.bill_status', ['C', 'X']);
        $query->where('p.current_step', 'K');

        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('p.name', 'like', '%' . $keyword . '%')
                        ->orWhere('p.bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->pur_type_id)) {
            $purTypes = $request->pur_type_id;
            $purTypeArr = is_array($purTypes) ? $purTypes : explode(',', trim($purTypes));
            $query->whereIn('p.pur_type_id', $purTypeArr);
        }
        if (!empty($request->bid_mode_id)) {
            $bidModeids = $request->bid_mode_id;
            $bidModeIdArr = is_array($bidModeids) ? $bidModeids : explode(',', trim($bidModeids));
            $query->whereIn('p.bid_mode_id', $bidModeIdArr);
        }
        if (!empty($request->bill_status) && $request->bill_status != 'WKS') {
            $billStatus = $request->bill_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('p.bill_status', $billStatusies);
        } elseif (!empty($request->bill_status) && $request->bill_status == 'WKS') {
            $query->whereNull('p.bill_status');
        }

        if (!empty($request->decision_status)) {
            $decisionStatus = $request->decision_status;
            $decisionStatusies = is_array($decisionStatus) ? $decisionStatus : explode(',', trim($decisionStatus));
            $query->whereIn('pd.decision_status', $decisionStatusies);
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('p.setup_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('p.setup_date', $createAts);
        }
        if (!empty($request->decisiontype)) {
            $decisionAts = $this->getTimeByType($request->decisiontype);
            $query->whereBetween('p.decision_at', $decisionAts);
        } elseif (!empty($request->decisiontime)) {
            $decisiontime = $request->decisiontime;
            $decisionAts = is_array($decisiontime) ? $decisiontime : explode(',', $decisiontime);
            !empty($decisionAts[1]) ? $decisionAts[1] = date('Y-m-d 23:59:59', strtotime($decisionAts[1])) : $decisionAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('pd.decision_at', $decisionAts);
        }
    }

    public function getDecisionStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '未开始';
            case 'B':
                return '已提交';
            case 'C':
                return '已完成';
            default :
                return '未开始';
        }
    }

    public function Init(int $projectId) {
        $admin = Auth::guard('admin')->user();
        $data = [
            'project_id' => $projectId,
            'decision_status' => 'A',
            'decision_at' => null,
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        ProjectDecision::upsert($data, ['project_id'], ['updated_by', 'decision_at', 'updated_at', 'decision_status']);
    }

    public function projectThanks($projectData, $supplierIds) {
        if ($projectData['bill_status'] !== 'C') {
            return;
        }
        $admin = Auth::guard('admin')->user();
        $thanksId = ProjectThanks::insertGetId([
                    'org_id' => $projectData['org_id'],
                    'project_id' => $projectData['id'],
                    'bid_mode_id' => $projectData['bid_mode_id'],
                    'template_id' => 0,
                    'name' => $projectData['name'] . '投标结果通知书',
                    'status' => 'C',
                    'enable' => 1,
                    'created_by' => $admin->user_id,
                    'updated_by' => $admin->user_id,
                    'publish_date' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $supplierObj = Supplier::whereIn('id', $supplierIds)
                ->where('deleted_flag', 'N')
                ->where('status', 'APPROVED')
                ->where('enable', '1')
                ->get();
        if (empty($supplierObj)) {
            return;
        }
        $supplierList = $supplierObj->toArray();
        $thanksDataList = [];
        foreach ($supplierList AS $supplier) {
            $newData = $projectData;
            $newData['supplier_name'] = $supplier['name'];
            $newContent = view('tpl.project_thanks', $newData)->toHtml();
            $thanksDataList[] = [
                'thanks_id' => $thanksId,
                'content' => $newContent,
                'supplier_id' => $supplier['id'],
                'created_by' => $admin->user_id,
                'updated_by' => $admin->user_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        if (!empty($thanksDataList)) {
            ProjectThanksEntry::insert($thanksDataList);
        }
    }

}
