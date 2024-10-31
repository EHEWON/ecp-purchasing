<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Message,
    MessageReceiver,
    Project\Project,
    Project\ProjectMember,
    Project\ProjectOpen,
    Project\ProjectOpenSupplier,
    Project\ProjectSub,
    Purchaser
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    BidModeRepo,
    Project\ProjectDecisionEntryRepo,
    Project\ProjectDecisionSupplierRepo,
    Project\ProjectOpenFileRepo,
    Project\ProjectOpenSupplierRepo,
    Project\ProjectRepo,
    UserRepo,
    OrgRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectOpenRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectOpen();
        parent::__construct($this->model);
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
                ->selectRaw('p.id,p.name,p.bill_status,p.current_step,p.bid_open_deadline,'
                        . 'p.commercial_doc_end_date,p.technical_doc_end_date,ps.evaluated_method,'
                        . 'p.bid_publish_date,p.bid_mode_id,p.is_separate_doc,p.org_id,'
                        . 'p.bid_document,ps.evaluate_decide_way_id,ps.doc_type,ps.bid_eval_template,'
                        . 'ps.bid_open_type,p.bid_decision,ps.charging_stage')
                ->first();
        if (empty($baseObj)) {
            check(false, '招标项目不存在');
        }
        $admin = Auth::guard('admin')->user();
        $base = $baseObj->toArray();

        (new ProjectMemberRepo)->setRespBusiness($base, 'id');
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId);
        $object = null;
        $projectRepo = new ProjectRepo();
        $base['doc_type_name'] = $projectRepo->getDocTypeText($base['doc_type']);
        $base['current_step_name'] = $projectRepo->getCurrentStepText($base['current_step']);
        $base['open_type_name'] = $projectRepo->getOpenType($base['bid_open_type']);
        (new BidModeRepo)->setBidMode($base, 'bid_mode_id', 'bid_mode_name');
        $base['evaluate_decide_name'] = $projectRepo->getEvaluateDecide($base['evaluate_decide_way_id']);
        (new ProjectMemberRepo)->setRespBusiness($base, 'id');
        (new OrgRepo)->setOrg($base, 'org_id', 'org_name');
        $data = ['base' => $base, 'open' => [], 'attachs' => [], 'suppliers' => '', 'scores' => []];
        $open = !empty($object) ? $object->toArray() : ['open_status' => '',
            'open_at' => null,
            'base_price' => null,
            'score_type' => null,
            'score_mode' => null,
            'eval_template' => $base['bid_eval_template'],
            'open_type_description' => '',
            'bid_open_type' => $base['bid_open_type'],
            'bid_open_deadline' => $base['bid_open_deadline'],
            'evaluate_decide_way_id' => $base['evaluate_decide_way_id'],
            'doc_type' => $base['doc_type'],
            'evaluated_method' => $base['evaluated_method'],
            'tech_weight' => null,
            'com_weight' => null,
            'evaluate_decide_way_id' => $base['evaluate_decide_way_id'],
            'created_by' => $admin->user_id,];
        $open['bid_open_deadline'] = $base['bid_open_deadline'];
        $files = (new ProjectOpenFileRepo)->getList($projectId);
        $data['attachs'] = !empty($files['attachs']) ? $files['attachs'] : [];
        (new UserRepo)->setUser($open, 'created_by', 'created_name');
        $open['open_type_name'] = $projectRepo->getOpenType($open['bid_open_type']);
        $open['open_status_name'] = $this->getOpenStatusText($open['open_status']);
        $data['open'] = $open;
        $data['entry'] = (new ProjectEntryRepo)->getList($projectId);
        $data['suppliers'] = (new ProjectOpenSupplierRepo)->getList($projectId);
        $data['process'] = $projectRepo->getProcess($projectId);
        return $data;
    }

    public function edited(int $projectId, Request $request) {
        $open = $request->open;
        $admin = Auth::guard('admin')->user();
        $project = Project::where('id', $projectId)->first();
        check(!empty($project), '招标项目不存在');
        check($project->current_step === 'H', '招标项目当前阶段不是开标');
        $openObj = ProjectOpen::where('project_id', $projectId)->first();
        check(empty($openObj) || $openObj->open_status == 'A', '您已提交开标,请勿重复提交');
        if ($open['open_status'] === 'C') {
            if (empty($request->supplier)) {
                check(false, '供应商回标信息不能为空');
            }
            $supplierIdObj = ProjectOpenSupplier::where('is_tender', '1')
                    ->where('project_id', $projectId)
                    ->pluck('supplier_id');
            if (empty($supplierIdObj)) {
                check(false, '供应商回标信息不能为空');
            }
            $supplierIds = $supplierIdObj->toArray();
            $isTenderNum = 0;
            foreach ($request->supplier as $supplier) {
                if (!isset($supplier['supplier_id']) || !in_array($supplier['supplier_id'], $supplierIds)) {
                    continue;
                }
                !isset($supplier['is_inval_id']) || $supplier['is_inval_id'] == '0' ? $isTenderNum += 1 : null;
            }
            if ($isTenderNum == 0) {
                check(false, '有效投标供应商不能为空');
            }
        }
        $data = [
            'project_id' => $projectId,
            'open_status' => !empty($open['open_status']) ? $open['open_status'] : 'A',
            'open_at' => !empty($open['open_status']) && $open['open_status'] === 'C' ? date('Y-m-d H:i:s') : null,
            'eval_template' => !empty($open['eval_template']) ? $open['eval_template'] : null,
            'score_mode' => !empty($open['score_mode']) ? $open['score_mode'] : 'A',
            'score_type' => !empty($open['score_type']) ? $open['score_type'] : 'A',
            'tech_weight' => !empty($open['tech_weight']) ? $open['tech_weight'] : null,
            'com_weight' => !empty($open['com_weight']) ? $open['com_weight'] : null,
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $flag = ProjectOpen::upsert($data, ['project_id'], ['updated_by', 'open_at', 'updated_at', 'open_status', 'eval_template', 'score_mode', 'score_type']);


        (new ProjectOpenSupplierRepo)->updateData($projectId, $request);
        if (!empty($open['open_status']) && $open['open_status'] === 'C') {
            $decideWay = ProjectSub::where('project_id', $projectId)->value('evaluate_decide_way_id');
            Project::where('id', $projectId)->update([
                'bid_open' => 1,
                'current_step' => $decideWay == '2' ? 'K' : 'I',
                'updated_by' => $admin->user_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            if ($decideWay == '2') {
                (new ProjectDecisionRepo)->init($projectId);
                (new ProjectDecisionEntryRepo)->init($projectId);
                (new ProjectDecisionSupplierRepo)->init($projectId, $decideWay);
            }
            $this->sends($projectId);
        }
        return $flag;
    }

    public function Init(int $projectId) {
        $dataList = [];
        $projectObj = Project::where('id', $projectId)
                ->first();
        if (empty($projectObj)) {
            return;
        }
        $sub = ProjectSub::where('project_id', $projectId)->first()->toArray();
        $project = $projectObj->toArray();
        $dataList[] = [
            'project_id' => $projectId,
            'org_id' => !empty($project['org_id']) ? $project['org_id'] : null,
            'bill_no' => !empty($project['bill_no']) ? $project['bill_no'] : null,
            'bill_status' => !empty($project['bill_status']) ? $project['bill_status'] : null,
            'evaluate_decide_way_id' => !empty($sub['evaluate_decide_way_id']) ? $sub['evaluate_decide_way_id'] : null,
            'evaluated_method' => !empty($sub['evaluated_method']) ? $sub['evaluated_method'] : null,
            'bid_open_deadline' => !empty($project['bid_open_deadline']) ? $project['bid_open_deadline'] : null,
            'open_status' => 'A',
            'open_type_description' => '',
            'bid_open_type' => !empty($sub['bid_open_type']) ? $sub['bid_open_type'] : null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        ProjectOpen::upsert($dataList, ['project_id'], ['open_status']);
        (new ProjectOpenSupplierRepo)->Init($projectId);
    }

    public function sends($projectId) {
        $ProjectObj = Project::where('id', $projectId)->first();
        if (empty($ProjectObj)) {
            return;
        }
        $ProjectData = $ProjectObj->toArray();
        $orgName = Purchaser::where('id', $ProjectData['org_id'])
                ->value('name');
        $userIdObj = ProjectMember::where('project_id', $projectId)
                ->whereRaw('find_in_set(\'I\',resp_business)')
                ->pluck('user_id');
        if (empty($userIdObj)) {
            return [];
        }
        $userIds = $userIdObj->toArray();
        $this->sendMail($ProjectData, $userIds, $orgName);
        $this->sendMessage($ProjectData, $projectId, $userIds, $orgName);
    }

    public function sendMessage($ProjectData, $projectId, $userIds, $orgName) {

        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/inviteTenders/EvaluationOfBid?id=' . $projectId,
                    'sender_id' => $ProjectData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . env('APP_NAME') . '】【' . $ProjectData['name'] . '】的评标通知！',
                    'message' => '您好，【' . $ProjectData['name'] . '】的招标项目已开标，请尽快登录系统完成评标。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (empty($userIds)) {
            return;
        }
        $dataList = [];
        foreach ($userIds as $userId) {
            $dataList[] = [
                'message_id' => $messageId,
                'receiver_id' => $userId,
                'org_id' => $ProjectData['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            MessageReceiver::insert($dataList);
        }
    }

    public function sendMail($ProjectData, $userIds, $orgName) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'projectData' => $ProjectData,
            'userIds' => $userIds,
            'orgName' => $orgName
                ], 'PROJECTOPEN'));
    }

    public function getOpenStatusText($status) {
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

}
