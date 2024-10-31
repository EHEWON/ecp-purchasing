<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Message,
    MessageReceiver,
    Project\Project,
    Project\ProjectPublish,
    Project\ProjectSub,
    Project\ProjectSupplier,
    Purchaser,
    User,
    UserSupplier
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    BidModeRepo,
    Project\ProjectDocFileRepo,
    Project\ProjectEntryRepo,
    Project\ProjectPublishFileRepo,
    Project\ProjectRepo,
    Project\ProjectSupplierRepo,
    UserRepo,
    OrgRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectPublishRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectPublish();
        parent::__construct($this->model);
    }

    public function info(int $projectId) {
        if (empty($projectId)) {
            return [];
        }
        $admin = Auth::guard('admin')->user();
        $project = (new Project)->getTable();
        $sub = (new ProjectSub)->getTable();
        $baseObj = Project::from($project . ' as p')
                ->join($sub . ' as ps', function($join) {
                    $join->on('ps.project_id', 'p.id');
                })
                ->where('p.id', $projectId)
                ->selectRaw('p.id,p.bill_no,p.name,p.org_id,p.bill_status,p.current_step,'
                        . 'p.commercial_doc_end_date,p.technical_doc_end_date,p.bid_decision,'
                        . 'p.bid_publish_date,p.bid_mode_id,p.bid_open_deadline,'
                        . 'p.bid_evaluation_date,p.bid_decision_date,ps.doc_type,ps.evaluate_decide_way_id')
                ->first();
        if (empty($baseObj)) {
            check(false, '招标项目不存在');
        }
        $base = $baseObj->toArray();
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId);
        $object = $qurey->orderBy('id', 'ASC')->first();
        $projectRepo = new ProjectRepo();
        $base['doc_type_name'] = $projectRepo->getDocTypeText($base['doc_type']);
        $base['current_step_name'] = $projectRepo->getCurrentStepText($base['current_step']);
        (new BidModeRepo)->setBidMode($base, 'bid_mode_id', 'bid_mode_name');
        $base['evaluate_decide_name'] = $projectRepo->getEvaluateDecide($base['evaluate_decide_way_id']);
        (new ProjectMemberRepo)->setRespBusiness($base, 'id');
        (new OrgRepo)->setOrg($base, 'org_id', 'org_name');
        $data = ['base' => $base, 'publish' => []];
        $publish = !empty($object) ? $object->toArray() : [
            'publish_status' => '',
            'publish_at' => null,
            'comment' => '',
            'created_by' => $admin->user_id,
        ];
        $publish['publish_status_name'] = $this->getPublishStatusText($publish['publish_status']);
        $files = (new ProjectPublishFileRepo)->getList($projectId);
        $data['technical'] = !empty($files['technical']) ? $files['technical'] : [];
        $data['commercial'] = !empty($files['commercial']) ? $files['commercial'] : [];
        $data['attachs'] = !empty($files['attachs']) ? $files['attachs'] : [];
        (new UserRepo)->setUser($publish, 'created_by', 'created_name');
        $data['publish'] = $publish;
        $data['entry'] = (new ProjectEntryRepo)->getList($projectId);
        $data['supplier'] = (new ProjectSupplierRepo)->getList($projectId, '', 'Y', $base['org_id']);
        $data['process'] = $projectRepo->getProcess($projectId);
        return $data;
    }

    public function edited(int $projectId, Request $request) {
        $publish = $request->publish;
        $admin = Auth::guard('admin')->user();
        $project = Project::where('id', $projectId)->first();
        check(!empty($project), '招标项目不存在');
        check($project->current_step === 'F', '招标项目当前阶段不是发标');
        $publishObj = ProjectPublish::where('project_id', $projectId)->first();
        check(empty($publishObj) || $publishObj->publish_status == 'A', '您已提交发标,请勿重复提交');
        $data = [
            'project_id' => $projectId,
            'publish_status' => !empty($publish['publish_status']) ? $publish['publish_status'] : 'A',
            'publish_at' => !empty($publish['publish_status']) && $publish['publish_status'] === 'C' ? date('Y-m-d H:i:s') : null,
            'comment' => !empty($publish['comment']) ? $publish['comment'] : '',
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $flag = ProjectPublish::upsert($data, ['project_id'], ['publish_status', 'publish_at', 'comment', 'updated_by', 'updated_at']);
        (new ProjectPublishFileRepo)->updateData($projectId, $request);
        if (!empty($publish['publish_status']) && $publish['publish_status'] === 'C') {
            Project::where('id', $projectId)->update([
                'bid_publish' => '1',
                'updated_by' => $admin->user_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            //修改供应商待投标状态
            ProjectSupplier::where('project_id', $projectId)->where('shortlist_flag', 'Y')->update([
                'status' => 'K',
            ]);
            $this->sends($projectId);
        }
        return $flag;
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
                    'content_url' => $bossUrl . '/front/#/supplierTenders/bidDetails?id=' . $projectId,
                    'sender_id' => $ProjectData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . env('APP_NAME') . '】发布【' . $ProjectData['name'] . '】的招标项目，标书已发布。',
                    'message' => '您好，发布【' . $ProjectData['name'] . '】标书已发布，请尽快登录系统完成标书下载。',
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
                ], 'PROJECTPUBLISH'));
    }

    public function getPublishStatusText($status) {
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

    public function init(int $projectId) {
        $currentStep = Project::where('id', $projectId)->value('current_step');
        if ($currentStep !== 'F') {
            return;
        }
        $data = [
            'project_id' => $projectId,
            'publish_status' => 'A',
            'publish_at' => null,
            'comment' => '',
            'created_by' => null,
            'updated_by' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $flag = ProjectPublish::upsert($data, ['project_id'], ['publish_status', 'publish_at', 'comment', 'updated_by', 'updated_at']);
        $files = (new ProjectDocFileRepo)->getList($projectId);
        $request = new Request();
        $request->merge($files);
        (new ProjectPublishFileRepo)->updateData($projectId, $request);
        return $flag;
    }

}
