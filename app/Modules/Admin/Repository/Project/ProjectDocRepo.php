<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\{
    Project,
    ProjectDocument,
    ProjectSub
};
use App\Modules\Admin\Repository\{
    BidModeRepo,
    Project\ProjectDocFileRepo,
    Project\ProjectEntryRepo,
    Project\ProjectPublishRepo,
    Project\ProjectRepo,
    UserRepo,
    OrgRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectDocRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectDocument();
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
                ->selectRaw('p.id,p.name,p.bill_status,p.current_step,p.commercial_doc_end_date,'
                        . 'p.technical_doc_end_date,p.bid_decision,p.org_id,'
                        . 'p.bid_publish_date,p.bid_mode_id,p.is_separate_doc,'
                        . 'p.bid_document,ps.evaluate_decide_way_id,ps.doc_type,p.enroll_deadline')
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
        (new BidModeRepo)->setBidMode($base, 'bid_mode_id', 'bid_mode_name');
        $base['evaluate_decide_name'] = $projectRepo->getEvaluateDecide($base['evaluate_decide_way_id']);
        (new ProjectMemberRepo)->setRespBusiness($base, 'id');
        (new OrgRepo)->setOrg($base, 'org_id', 'org_name');
        $data = ['base' => $base, 'doc' => []];

        $doc = !empty($object) ? $object->toArray() : [
            'bill_status' => '',
            'document_at' => null,
            'created_by' => $admin->user_id,
        ];
        $doc['doc_status_name'] = $this->getDocStatusText($doc['bill_status']);
        $files = (new ProjectDocFileRepo)->getList($projectId);
        $doc['technical'] = !empty($files['technical']) ? $files['technical'] : [];
        $doc['commercial'] = !empty($files['commercial']) ? $files['commercial'] : [];
        $doc['attachs'] = !empty($files['attachs']) ? $files['attachs'] : [];
        (new UserRepo)->setUser($doc, 'created_by', 'created_name');
        $data['doc'] = $doc;
        $data['entry'] = (new ProjectEntryRepo)->getList($projectId);
        $data['process'] = $projectRepo->getProcess($projectId);
        return $data;
    }

    public function edited(int $projectId, Request $request) {
        $document = $request->doc;
        $admin = Auth::guard('admin')->user();
        $project = Project::where('id', $projectId)->first();
        check(!empty($project), '招标项目不存在');
        $currentSteps = explode(',', $project->current_step);
        check(in_array('C', $currentSteps), '招标项目当前阶段不是标书编制');
        $docObj = ProjectDocument::where('project_id', $projectId)->first();
        check(empty($docObj) || $docObj->bill_status == 'A', '您已提交标书编制,请勿重复提交');
        if ($document['bill_status'] === 'C') {
            check($project->enroll_deadline < date('Y-m-d H:i:s'), '未到确认截止时间，您不能操作标书编制');
        }
        $data = [
            'project_id' => $projectId,
            'bill_status' => !empty($document['bill_status']) ? $document['bill_status'] : 'A',
            'document_at' => !empty($document['bill_status']) && $document['bill_status'] === 'C' ? date('Y-m-d H:i:s') : null,
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $flag = ProjectDocument::upsert($data, ['project_id'], ['updated_by', 'org_id', 'document_at', 'updated_at', 'bill_status']);
        (new ProjectDocFileRepo)->updateData($projectId, $request);
        if (!empty($document['bill_status']) && $document['bill_status'] === 'C') {
            $invitation = Project::where('id', $projectId)->value('supplier_invitation');
            $data = [
                'bid_document' => '1',
                'updated_by' => $admin->user_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($invitation == '1') {
                $data['current_step'] = 'F';
            } else {
                $data['current_step'] = 'B';
            }
            Project::where('id', $projectId)->update($data);
            (new ProjectPublishRepo)->init($projectId);
        }
        return $flag;
    }

    public function getDocStatusText($status) {
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
