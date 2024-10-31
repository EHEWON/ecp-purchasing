<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\ProjectSub;
use App\Modules\Admin\Repository\{
    Project\ProjectRepo
};
use Illuminate\Http\Request;

class ProjectSubRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectSub();
        parent::__construct($this->model);
    }

    public function info(int $projectId) {
        if (empty($projectId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId);
        $object = $qurey->orderBy('id', 'ASC')->first();
        if (empty($object)) {
            return [];
        }
        $sub = $object->toArray();
        $projectRepo = new ProjectRepo();
        $sub['total_control'] = is_null($sub['total_control']) ? null : number_format($sub['total_control'], 2, '.', '');
        $sub['total_ctrl_exc_vat'] = is_null($sub['total_ctrl_exc_vat']) ? null : number_format($sub['total_ctrl_exc_vat'], 2, '.', '');
        $sub['tender_fee'] = is_null($sub['tender_fee']) ? null : number_format($sub['tender_fee'], 2, '.', '');
        $sub['deposit'] = is_null($sub['deposit']) ? null : number_format($sub['deposit'], 2, '.', '');
        $sub['open_type_name'] = $projectRepo->getOpenType($sub['bid_open_type']);
        $sub['doc_type_name'] = $projectRepo->getDocTypeText($sub['doc_type']);
        $sub['evaluate_decide_name'] = $projectRepo->getEvaluateDecide($sub['evaluate_decide_way_id']);
        $sub['evaluated_method_name'] = $projectRepo->getEvaluatedMethod($sub['evaluated_method']);

        return $sub;
    }

    public function updateData(int $projectId, Request $request) {
        ProjectSub::where('project_id', $projectId)->delete();
        $projectSub = $this->getProjectSub($projectId, $request);
        if (!empty($projectSub)) {
            ProjectSub::upsert($projectSub, ['project_id'], [
                'total_control',
                'total_ctrl_exc_vat',
                'is_rate_bidding',
                'invitation_deadline',
                'is_allow_revoke',
                'is_material_pur',
                'bid_bus_talk',
                'bid_bus_talk_date',
                'bid_open_type',
                'tender_fee',
                'deposit',
                'is_deposit',
                'need_flag_new_supplier',
                'entity_type_id',
                'doc_type',
                'evaluate_decide_way_id',
                'tech_weight',
                'com_weight',
                'is_online_eval',
                'bid_eval_template',
                'score_mode',
                'score_type',
                'extract_reco_id',
                'bid_bottom_make',
                'clarificaiton',
                'bid_bottom_make_date',
                'clarificaiton_date',
                'evaluated_method',
                'charging_stage',
                'is_supplier_get',
                'current_bill_no',
                'id_and_status',
                'modify_time',
            ]);
        }
    }

    public function getProjectSub(int $projectId, Request $request) {

        $sub = $request->base;
        if ((!empty($sub['charging_stage']) && $sub['charging_stage'] == '1')) {
            $sub['deposit'] = 0;
        }
        if ($sub['bid_mode_id'] == '1') {
            $sub['is_supplier_get'] = 'N';
        }
        return[
            'project_id' => $projectId,
            'total_control' => !empty($sub['total_control']) ? $sub['total_control'] : '0',
            'total_ctrl_exc_vat' => !empty($sub['total_ctrl_exc_vat']) ? $sub['total_ctrl_exc_vat'] : '0',
            'is_rate_bidding' => !empty($sub['is_rate_bidding']) ? $sub['is_rate_bidding'] : '0',
            'invitation_deadline' => !empty($sub['invitation_deadline']) ? $sub['invitation_deadline'] : null,
            'is_allow_revoke' => !empty($sub['is_allow_revoke']) ? $sub['is_allow_revoke'] : '0',
            'is_material_pur' => !empty($sub['is_material_pur']) ? $sub['is_material_pur'] : '0',
            'bid_bus_talk' => !empty($sub['bid_bus_talk']) ? $sub['bid_bus_talk'] : '',
            'bid_bus_talk_date' => !empty($sub['bid_bus_talk_date']) ? $sub['bid_bus_talk_date'] : null,
            'bid_open_type' => !empty($sub['bid_open_type']) ? $sub['bid_open_type'] : null,
            'tender_fee' => !empty($sub['tender_fee']) ? $sub['tender_fee'] : '0',
            'deposit' => !empty($sub['deposit']) ? $sub['deposit'] : '0',
            'is_deposit' => !empty($sub['is_deposit']) ? $sub['is_deposit'] : '0',
            'need_flag_new_supplier' => !empty($sub['need_flag_new_supplier']) ? $sub['need_flag_new_supplier'] : '0',
            'entity_type_id' => !empty($sub['entity_type_id']) ? $sub['entity_type_id'] : '',
            'doc_type' => !empty($sub['doc_type']) ? $sub['doc_type'] : '',
            'evaluate_decide_way_id' => !empty($sub['evaluate_decide_way_id']) ? $sub['evaluate_decide_way_id'] : null,
            'tech_weight' => !empty($sub['tech_weight']) ? $sub['tech_weight'] : '0',
            'com_weight' => !empty($sub['com_weight']) ? $sub['com_weight'] : '0',
            'is_online_eval' => '1',
            'bid_eval_template' => !empty($sub['bid_eval_template']) ? $sub['bid_eval_template'] : '0',
            'score_mode' => !empty($sub['score_mode']) ? $sub['score_mode'] : '',
            'score_type' => !empty($sub['score_type']) ? $sub['score_type'] : '',
            'extract_reco_id' => !empty($sub['extract_reco_id']) ? $sub['extract_reco_id'] : '',
            'bid_bottom_make' => !empty($sub['bid_bottom_make']) ? $sub['bid_bottom_make'] : '0',
            'clarificaiton' => !empty($sub['clarificaiton']) ? $sub['clarificaiton'] : '0',
            'bid_bottom_make_date' => !empty($sub['bid_bottom_make_date']) ? $sub['bid_bottom_make_date'] : null,
            'clarificaiton_date' => !empty($sub['clarificaiton_date']) ? $sub['clarificaiton_date'] : null,
            'evaluated_method' => !empty($sub['evaluated_method']) ? $sub['evaluated_method'] : null,
            'charging_stage' => !empty($sub['charging_stage']) ? $sub['charging_stage'] : '2',
            'is_supplier_get' => !empty($sub['is_supplier_get']) ? $sub['is_supplier_get'] : '0',
            'current_bill_no' => !empty($sub['current_bill_no']) ? $sub['current_bill_no'] : '0',
            'id_and_status' => !empty($sub['id_and_status']) ? $sub['id_and_status'] : '0',
            'create_time' => date('Y-m-d H:i:s'),
            'modify_time' => date('Y-m-d H:i:s'),
        ];
    }

}
