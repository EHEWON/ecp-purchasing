<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Common\Models\{
    Project\Project,
    Project\ProjectSupplier,
    Project\ProjectSub,
    Message,
    Supplier,
    MessageReceiver,
    Purchaser,
    User,
    Notice,
    NoticeSub,
    UserSupplier,
    Project\ProjectPublish,
    Project\ProjectDocument,
    Project\ProjectDecision,
    Project\ProjectMember,
    Project\ProjectInvitation,
    Project\ProjectInvitationEntry
};
use App\Modules\Admin\Repository\{
    UserRepo,
    BidModeRepo,
    PurTypeRepo,
    ValuationModeRepo,
    NoticeManageRepo,
    Project\AttachRepo,
    Project\ProjectSubRepo,
    Project\ProjectMemberRepo,
    Project\ProjectSupplierRepo,
    OrgRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use App\Jobs\SendMailJob;
use Illuminate\Support\Facades\DB;

class ProjectRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'name',
        'bill_status',
        'setup_date',
        'pur_type_id',
        'bid_mode_id',
        'bid_publish_date',
        'answered_at',
        'answer_complete_at',
        'bid_open_deadline',
        'bid_evaluation_date',
    ];

    public function __construct() {
        $this->model = new Project();
        parent::__construct($this->model);
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'created_at';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'created_at') {
            $query->orderBy('created_at', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = 'id,bill_no,'
    . 'name,bill_status,org_id,setup_date,created_by,created_at,'
    . 'updated_at,updated_by,current_step,bid_publish_date,bid_publish,bid_document,'
    . 'pur_type_id,pur_mode,bid_mode_id,bid_evaluation,bid_open,bid_decision,'
    . 'bid_open_deadline,bid_evaluation_date,pur_project_set,'
    . 'invalided_at,invalided_by,invalided_reason') {
        $query = $this->model
                ->selectRaw($filed);
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            $item['current_step_name'] = $this->getCurrentStepText($item['current_step']);
        }
        (new OrgRepo)->setOrgs($data, 'org_id', 'org_name');
        (new ProjectMemberRepo)->setRespBusinesies($data, 'id');
        (new UserRepo)->setUsers($data, 'created_by', 'creator_name');
        (new UserRepo)->setUsers($data, 'invalided_by', 'invalided_name');
        (new BidModeRepo)->setBidModes($data, 'bid_mode_id', 'bid_mode_name');
        (new PurTypeRepo)->setPurTypes($data, 'pur_type_id', 'pur_type_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function getTotal(Request $request) {
        $query = Project::whereRaw('1=1');
        $this->getWhere($query, $request);
        return $query->count();
    }

    public function todoTotal() {
        $query = Project::where('bill_status', 'A');
        return $query->count();
    }

    public function decisionTotal() {
        $query = Project::whereIn('bill_status', ['C', 'X'])
                ->where('current_step', 'K')
                ->where(function($q) {
            $q->where('bid_decision', '0')
            ->orWhere('bid_decision', '')
            ->orWhereNull('bid_decision');
        });
        return $query->count();
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {
        $query = $this->model->selectRaw('*');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $base = $object->toArray();
        $base['bill_status_name'] = $this->getBillStatusText($base['bill_status']);
        $base['current_step_name'] = $this->getCurrentStepText($base['current_step']);
        (new BidModeRepo)->setBidMode($base, 'bid_mode_id', 'bid_mode_name');
        (new ValuationModeRepo)->setValuationMode($base, 'bid_valuation_id', 'bid_valuation_name');
        $base['entrustment_way_name'] = $this->getEntrustmentWay($base['entrustment_way']);
        (new PurTypeRepo)->setPurType($base, 'pur_type_id', 'pur_type_name');
        (new UserRepo)->setUser($base, 'created_by', 'created_name');
        (new UserRepo)->setUser($base, 'audited_by', 'audited_name');
        (new UserRepo)->setUser($base, 'updated_by', 'updated_name');
        (new UserRepo)->setUser($base, 'invalided_by', 'invalidor_name');
        (new UserRepo)->setUser($base, 'contact_id', 'contact_name');
        (new ProjectMemberRepo)->setRespBusiness($base, 'id');
        (new OrgRepo)->setOrg($base, 'org_id', 'org_name');
        $data = [];
        $data['base'] = $base;
        $sub = (new ProjectSubRepo)->info($id);
        if (!empty($sub) && $base) {
            unset($sub['id']);
            $data['base'] = array_merge($base, $sub);
        }
        $data['attach'] = (new AttachRepo)->getList($id);
        $data['member'] = (new ProjectMemberRepo)->getList($id);
        $data['entry'] = (new ProjectEntryRepo)->getList($id);
        $data['supplier'] = (new ProjectSupplierRepo)->getList($id, '', null, $base['org_id']);
        $data['process'] = $this->getProcess($id);
        $data['shortListAttach'] = (new AttachRepo)->getShortList($id);
        return $data;
    }

    public function invalidInfo($id) {
        $query = $this->model->selectRaw('invalided_at,invalided_by,invalided_reason,bill_status');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $base = $object->toArray();
        $base['bill_status_name'] = $this->getBillStatusText($base['bill_status']);
        (new UserRepo)->setUser($base, 'invalided_by', 'invalided_name');
        return $base;
    }

    /**
     * @param int $projectId
     * @param Request $request
     * 
     * @return array
     */
    public function edited($projectId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $base = $request->base;
        if (!empty($base['invitation_deadline'])) {
            $base['invitation_deadline'] = date('Y-m-d H:i:s', strtotime($base['invitation_deadline']));
        }
        if (!empty($base['supplier_invi_end_date'])) {
            $base['supplier_invi_end_date'] = date('Y-m-d H:i:s', strtotime($base['supplier_invi_end_date']));
        }
        if (!empty($base['commercial_doc_end_date'])) {
            $base['commercial_doc_end_date'] = date('Y-m-d H:i:s', strtotime($base['commercial_doc_end_date']));
        }
        if (!empty($base['technical_doc_end_date'])) {
            $base['technical_doc_end_date'] = date('Y-m-d H:i:s', strtotime($base['technical_doc_end_date']));
        }
        if (!empty($base['bid_open_deadline'])) {
            $base['bid_open_deadline'] = date('Y-m-d H:i:s', strtotime($base['bid_open_deadline']));
        }
        if (!empty($base['bid_publish_date'])) {
            $base['bid_publish_date'] = date('Y-m-d', strtotime($base['bid_publish_date']));
        }
        if (!empty($base['bid_evaluation_date'])) {
            $base['bid_evaluation_date'] = date('Y-m-d', strtotime($base['bid_evaluation_date']));
        }
        if (!empty($base['bid_decision_date'])) {
            $base['bid_decision_date'] = date('Y-m-d', strtotime($base['bid_decision_date']));
        }
        if (!empty($base['approach_date'])) {
            $base['approach_date'] = date('Y-m-d', strtotime($base['approach_date']));
        }
        $ProjectData = [
            'bill_no' => !empty($base['bill_no']) ? $base['bill_no'] : null,
            'bill_status' => !empty($base['bill_status']) ? $base['bill_status'] : 'A',
            'org_id' => !empty($base['org_id']) ? $base['org_id'] : 1,
            'name' => !empty($base['name']) ? $base['name'] : '',
            'pur_type_id' => !empty($base['pur_type_id']) ? $base['pur_type_id'] : null,
            'pur_mode' => !empty($base['pur_mode']) ? $base['pur_mode'] : '',
            'setup_date' => !empty($base['setup_date']) ? $base['setup_date'] : null,
            'entrustment_supplier' => !empty($base['entrustment_supplier']) ? $base['entrustment_supplier'] : null,
            'bid_valuation_id' => !empty($base['bid_valuation_id']) ? $base['bid_valuation_id'] : null,
            'enroll_deadline' => !empty($base['invitation_deadline']) ? $base['invitation_deadline'] : null,
            'design_drawing_end_date' => !empty($base['design_drawing_end_date']) ? $base['design_drawing_end_date'] : null,
            'supplier_invi_end_date' => !empty($base['supplier_invi_end_date']) ? $base['supplier_invi_end_date'] : null,
            'technical_doc_end_date' => !empty($base['technical_doc_end_date']) ? $base['technical_doc_end_date'] : null,
            'commercial_doc_end_date' => !empty($base['commercial_doc_end_date']) ? $base['commercial_doc_end_date'] : null,
            'bid_publish_date' => !empty($base['bid_publish_date']) ? $base['bid_publish_date'] : null,
            'bid_open_deadline' => !empty($base['bid_open_deadline']) ? $base['bid_open_deadline'] : null,
            'bid_decision_date' => !empty($base['bid_decision_date']) ? $base['bid_decision_date'] : null,
            'approach_date' => !empty($base['approach_date']) ? $base['approach_date'] : null,
            'contact_id' => !empty($base['contact_id']) ? $base['contact_id'] : null,
            'contact_tel' => !empty($base['contact_tel']) ? $base['contact_tel'] : '',
            'fax' => !empty($base['fax']) ? $base['fax'] : '',
            'address' => !empty($base['address']) ? $base['address'] : '',
            'email' => !empty($base['email']) ? $base['email'] : '',
            'enable_multi_section' => !empty($base['enable_multi_section']) ? $base['enable_multi_section'] : '',
            'bid_mode_id' => !empty($base['bid_mode_id']) ? $base['bid_mode_id'] : 1,
            'pur_project_set' => !empty($base['pur_project_set']) ? $base['pur_project_set'] : '通用项目',
            'current_step' => 'A',
            'bid_project' => 1,
            'supplier_invitation' => 0,
            'bid_document' => 0,
            'bid_publish' => 0,
            'bid_open' => 0,
            'bid_evaluation' => 0,
            'bid_decision' => 0,
            'entrustment_org_unit' => !empty($base['entrustment_org_unit']) ? $base['entrustment_org_unit'] : 0,
            'is_separate_doc' => !empty($base['is_separate_doc']) ? $base['is_separate_doc'] : 0,
            'qualification_required' => !empty($base['qualification_required']) ? $base['qualification_required'] : null,
            'entrustment_way' => !empty($base['entrustment_way']) ? $base['entrustment_way'] : '1',
            'bid_evaluation_date' => !empty($base['bid_evaluation_date']) ? $base['bid_evaluation_date'] : null,
            'source_project_id' => !empty($base['source_project_id']) ? $base['source_project_id'] : '',
            'answered_at' => !empty($base['answered_at']) ? $base['answered_at'] : 0,
            'answered_at' => !empty($base['answered_at']) ? $base['answered_at'] : null,
            'entity_type_id' => !empty($base['entity_type_id']) ? $base['entity_type_id'] : '',
            'answer_complete_at' => !empty($base['answer_complete_at']) ? $base['answer_complete_at'] : null,
            'click_content' => !empty($base['click_content']) ? $base['click_content'] : '',
            'bid_bus_talk' => !empty($base['bid_bus_talk']) ? $base['bid_bus_talk'] : 0,
            'enable_list' => !empty($base['enable_list']) ? $base['enable_list'] : '0',
            'required_list' => !empty($base['required_list']) ? $base['required_list'] : 0,
            'is_filter' => !empty($base['is_filter']) ? $base['is_filter'] : 0,
            'required_cat' => !empty($base['required_cat']) ? $base['required_cat'] : '',
            'required_evel' => !empty($base['required_evel']) ? $base['required_evel'] : '',
            'registered_capital' => !empty($base['registered_capital']) ? $base['registered_capital'] : null,
            'license_requirements' => !empty($base['license_requirements']) ? $base['license_requirements'] : '',
            'pur_description' => !empty($base['pur_description']) ? $base['pur_description'] : '',
            'updated_at' => date('Y-m-d H:i:s'),
            'setup_date' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id,
        ];

        if ($ProjectData['bill_status'] === 'C') {
            $ProjectData['current_step'] = 'B,C';
        }
        $flag = Project::where('id', $projectId)->update($ProjectData);
        (new ProjectSubRepo)->updateData($projectId, $request);
        (new AttachRepo)->updateData($projectId, $request);
        (new ProjectSupplierRepo)->updateData($projectId, $request);
        (new ProjectEntryRepo)->updateData($projectId, $request);
        (new ProjectMemberRepo())->updateData($projectId, $request);
        if ($ProjectData['bill_status'] !== 'C') {
            return $flag;
        }
        $ProjectData['id'] = $projectId;
        (new UserRepo())->setUser($ProjectData, 'contact_id', 'contact_name');
        (new PurTypeRepo)->setPurType($ProjectData, 'pur_type_id', 'pur_type_name');
        $this->sends($ProjectData, $projectId);
        return $flag;
    }

    /**
     * @param int $ProjectId
     * @param Request $request
     * 
     * @return array
     */
    public function add(Request $request) {
        $base = $request->base;
        $admin = Auth::guard('admin')->user();
        if (!empty($base['invitation_deadline'])) {
            $base['invitation_deadline'] = date('Y-m-d H:i:s', strtotime($base['invitation_deadline']));
        }
        if (!empty($base['supplier_invi_end_date'])) {
            $base['supplier_invi_end_date'] = date('Y-m-d H:i:s', strtotime($base['supplier_invi_end_date']));
        }
        if (!empty($base['commercial_doc_end_date'])) {
            $base['commercial_doc_end_date'] = date('Y-m-d H:i:s', strtotime($base['commercial_doc_end_date']));
        }
        if (!empty($base['technical_doc_end_date'])) {
            $base['technical_doc_end_date'] = date('Y-m-d H:i:s', strtotime($base['technical_doc_end_date']));
        }
        if (!empty($base['bid_open_deadline'])) {
            $base['bid_open_deadline'] = date('Y-m-d H:i:s', strtotime($base['bid_open_deadline']));
        }
        if (!empty($base['bid_publish_date'])) {
            $base['bid_publish_date'] = date('Y-m-d', strtotime($base['bid_publish_date']));
        }
        if (!empty($base['bid_evaluation_date'])) {
            $base['bid_evaluation_date'] = date('Y-m-d', strtotime($base['bid_evaluation_date']));
        }
        if (!empty($base['bid_decision_date'])) {
            $base['bid_decision_date'] = date('Y-m-d', strtotime($base['bid_decision_date']));
        }
        if (!empty($base['approach_date'])) {
            $base['approach_date'] = date('Y-m-d', strtotime($base['approach_date']));
        }
        $ProjectData = [
            'bill_no' => !empty($base['bill_no']) ? $base['bill_no'] : null,
            'bill_status' => !empty($base['bill_status']) ? $base['bill_status'] : 'A',
            'org_id' => !empty($base['org_id']) ? $base['org_id'] : 1,
            'name' => !empty($base['name']) ? $base['name'] : '',
            'pur_type_id' => !empty($base['pur_type_id']) ? $base['pur_type_id'] : null,
            'pur_mode' => !empty($base['pur_mode']) ? $base['pur_mode'] : '',
            'setup_date' => !empty($base['setup_date']) ? $base['setup_date'] : null,
            'entrustment_supplier' => !empty($base['entrustment_supplier']) ? $base['entrustment_supplier'] : null,
            'bid_valuation_id' => !empty($base['bid_valuation_id']) ? $base['bid_valuation_id'] : null,
            'enroll_deadline' => !empty($base['invitation_deadline']) ? $base['invitation_deadline'] : null,
            'design_drawing_end_date' => !empty($base['design_drawing_end_date']) ? $base['design_drawing_end_date'] : null,
            'supplier_invi_end_date' => !empty($base['supplier_invi_end_date']) ? $base['supplier_invi_end_date'] : null,
            'technical_doc_end_date' => !empty($base['technical_doc_end_date']) ? $base['technical_doc_end_date'] : null,
            'commercial_doc_end_date' => !empty($base['commercial_doc_end_date']) ? $base['commercial_doc_end_date'] : null,
            'bid_publish_date' => !empty($base['bid_publish_date']) ? $base['bid_publish_date'] : null,
            'bid_open_deadline' => !empty($base['bid_open_deadline']) ? $base['bid_open_deadline'] : null,
            'bid_decision_date' => !empty($base['bid_decision_date']) ? $base['bid_decision_date'] : null,
            'approach_date' => !empty($base['approach_date']) ? $base['approach_date'] : null,
            'contact_id' => !empty($base['contact_id']) ? $base['contact_id'] : null,
            'contact_tel' => !empty($base['contact_tel']) ? $base['contact_tel'] : '',
            'fax' => !empty($base['fax']) ? $base['fax'] : '',
            'address' => !empty($base['address']) ? $base['address'] : '',
            'email' => !empty($base['email']) ? $base['email'] : '',
            'enable_multi_section' => !empty($base['enable_multi_section']) ? $base['enable_multi_section'] : '',
            'bid_mode_id' => !empty($base['bid_mode_id']) ? $base['bid_mode_id'] : 1,
            'pur_project_set' => !empty($base['pur_project_set']) ? $base['pur_project_set'] : '通用项目',
            'current_step' => 'A',
            'bid_project' => 1,
            'supplier_invitation' => 0,
            'bid_document' => 0,
            'bid_publish' => 0,
            'bid_open' => 0,
            'bid_evaluation' => 0,
            'bid_decision' => 0,
            'entrustment_org_unit' => !empty($base['entrustment_org_unit']) ? $base['entrustment_org_unit'] : 0,
            'is_separate_doc' => !empty($base['is_separate_doc']) ? $base['is_separate_doc'] : 0,
            'qualification_required' => !empty($base['qualification_required']) ? $base['qualification_required'] : null,
            'entrustment_way' => !empty($base['entrustment_way']) ? $base['entrustment_way'] : '1',
            'bid_evaluation_date' => !empty($base['bid_evaluation_date']) ? $base['bid_evaluation_date'] : null,
            'source_project_id' => !empty($base['source_project_id']) ? $base['source_project_id'] : '',
            'entity_type_id' => !empty($base['entity_type_id']) ? $base['entity_type_id'] : '',
            'required_list' => !empty($base['required_list']) ? $base['required_list'] : 0,
            'is_filter' => !empty($base['is_filter']) ? $base['is_filter'] : 0,
            'required_cat' => !empty($base['required_cat']) ? $base['required_cat'] : '',
            'required_evel' => !empty($base['required_evel']) ? $base['required_evel'] : '',
            'registered_capital' => !empty($base['registered_capital']) ? $base['registered_capital'] : null,
            'license_requirements' => !empty($base['license_requirements']) ? $base['license_requirements'] : '',
            'pur_description' => !empty($base['pur_description']) ? $base['pur_description'] : '',
            'setup_date' => date('Y-m-d H:i:s'),
            'created_by' => $admin->user_id,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if ($ProjectData['bill_status'] === 'C') {
            $ProjectData['current_step'] = 'B,C';
        }
        $projectId = Project::insertGetId($ProjectData);
        (new ProjectSubRepo)->updateData($projectId, $request);
        (new AttachRepo)->updateData($projectId, $request);
        (new ProjectSupplierRepo)->updateData($projectId, $request);
        (new ProjectEntryRepo)->updateData($projectId, $request);
        (new ProjectMemberRepo())->updateData($projectId, $request);
        if ($ProjectData['bill_status'] !== 'C') {
            return $projectId;
        }
        $ProjectData['id'] = $projectId;
        $ProjectData['tender_fee'] = !empty($base['tender_fee']) ? $base['tender_fee'] : '';
        $ProjectData['deposit'] = !empty($base['deposit']) ? $base['deposit'] : '';
        (new UserRepo())->setUser($ProjectData, 'contact_id', 'contact_name');
        (new PurTypeRepo)->setPurType($ProjectData, 'pur_type_id', 'pur_type_name');
        $this->sends($ProjectData, $projectId);
        return $projectId;
    }

    public function sends($ProjectData, $projectId) {
        if ($ProjectData['bill_status'] !== 'C') {
            return;
        }
        $orgName = Purchaser::where('id', $ProjectData['org_id'])
                ->value('name');
        $data = $this->notice($ProjectData);
        $nrequest = (new Request);
        $nrequest->merge($data);
        (new NoticeManageRepo)->addData($nrequest);
        if ($ProjectData['bid_mode_id'] == 1) {
            return true;
        }
        $supplierIds = ProjectSupplier::where('project_id', $projectId)
                ->where('deleted_flag', 'N')
                ->pluck('supplier_id');
        if (empty($supplierIds)) {
            return [];
        }
        $this->projectInvitation($ProjectData, $supplierIds);
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

    public function sendMail($ProjectData, $supplierIds, $orgName) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'projectData' => $ProjectData,
            'supplierIds' => $supplierIds,
            'orgName' => $orgName
                ], 'PROJECT'));
    }

    public function projectInvitation($projectData, $supplierIds) {

        if ($projectData['bill_status'] !== 'C' || $projectData['bid_mode_id'] != 2) {
            return;
        }
        $invitationId = ProjectInvitation::insertGetId([
                    'number' => $projectData['name'] . '邀请函',
                    'org_id' => $projectData['org_id'],
                    'project_id' => $projectData['id'],
                    'template_id' => 0,
                    'name' => $projectData['name'] . '邀请函',
                    'deadline_date' => $projectData['enroll_deadline'],
                    'status' => 'C',
                    'enable' => 1,
                    'publish_date' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $pSupplierObj = ProjectSupplier::whereIn('supplier_id', $supplierIds)
                ->where('project_id', $projectData['id'])
                ->selectRaw('supplier_id,supplier_deposit')
                ->get();
        $supplierObj = Supplier::whereIn('id', $supplierIds)
                ->where('deleted_flag', 'N')
                ->where('status', 'APPROVED')
                ->where('enable', '1')
                ->get();
        if (empty($supplierObj)) {
            return;
        }
        $supplierList = $supplierObj->toArray();
        $pSupplierList = !empty($pSupplierObj) ? $pSupplierObj->toArray() : [];
        $pSupplierArr = array_column($pSupplierList, 'supplier_deposit', 'supplier_id');
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

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        $admin = Auth::guard('admin')->user();
        $orgId = $this->getPPurchaserId();
        $count = Project::whereIn('id', $request->ids)
                ->count();
        $countN = Project::whereIn('id', $request->ids)
                ->whereIn('bill_status', ['B', 'C'])
                ->count();
        $countX = Project::whereIn('id', $request->ids)
                ->where('bill_status', 'X')
                ->count();
        $member = (new ProjectMember)->getTable();
        $project = (new Project)->getTable();
        $countP = Project::whereIn('p.id', $request->ids)
                ->from($project . ' as p')
                ->whereIn('p.bill_status', ['A'])
                ->whereNot(function ($q)use ($admin, $member) {
                    $q->where('p.contact_id', $admin->user_id)
                    ->orWhere('p.created_by', $admin->user_id)
                    ->orWhereRaw('EXISTS(SELECT id FROM ' . $member
                            . ' AS m where m.project_id=p.id '
                            . ' AND FIND_IN_SET(\'A\',m.resp_business )'
                            . ' AND m.user_id=' . $admin->user_id . ')');
                })
                ->count();
        DB::beginTransaction();
        $flag = Project::whereIn('id', $ids)
                ->where('bill_status', 'A')
                ->where(function ($q)use ($admin, $member) {
                    $q->where('contact_id', $admin->user_id)
                    ->orWhere('created_by', $admin->user_id)
                    ->orWhereRaw('EXISTS(SELECT id FROM ' . $member
                            . ' AS m where m.project_id=project.id '
                            . ' AND FIND_IN_SET(\'A\',m.resp_business )'
                            . ' AND m.user_id=' . $admin->user_id . ')');
                })
                ->delete();
        $str = '';
        if (!empty($flag)) {
            $str .= '成功删除' . $flag . '条';
        }
        if (!empty($countX)) {
            $str .= (!empty($str) ? '，' : '') . '已流标不能删除的招标' . $countX . '条';
        }

        if (!empty($countN)) {
            $str .= (!empty($str) ? '，' : '') . '已审核不能删除的招标' . $countN . '条';
        }
        if (!empty($countP)) {
            $str .= (!empty($str) ? '，' : '') . '不是创建人、联系人、经办业务人的不能删除的招标' . $countP . '条';
        }
        DB::commit();
        check($count === $flag, $str);
        return $flag ? 200 : $flag;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function invalid(Request $request) {
        $ids = $request->ids;
        $admin = Auth::guard('admin')->user();
        $invalidedReason = $request->invalided_reason;
        if (empty($invalidedReason)) {
            check(false, '处理意见不能为空');
        }
        $project = Project::whereIn('id', $ids)->first();
        if (empty($project)) {
            check(false, '招标不存在');
        }
        check($project->bill_status !== 'A', '暂存状态的招标不能作废');
        check($project->bill_status !== 'X', '该招标已经作废');
        DB::beginTransaction();
        ProjectSupplier::where('project_id', $project->id)
                ->where('enroll_status', 'Y')
                ->update(['status' => 'X']);
        $flag = Project::where('id', $project->id)
                ->update(['bill_status' => 'X',
            'invalided_by' => $admin->user_id,
            'invalided_reason' => $invalidedReason,
            'invalided_at' => date('Y-m-d H:i:s'),
        ]);
        DB::commit();
        return $flag ? 200 : $flag;
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
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->pur_type_id)) {
            $purTypes = $request->pur_type_id;
            $purTypeArr = is_array($purTypes) ? $purTypes : explode(',', trim($purTypes));
            $query->whereIn('pur_type_id', $purTypeArr);
        }
        if (!empty($request->bid_mode_id)) {
            $bidModeids = $request->bid_mode_id;
            $bidModeIdArr = is_array($bidModeids) ? $bidModeids : explode(',', trim($bidModeids));
            $query->whereIn('bid_mode_id', $bidModeIdArr);
        }
        if (!empty($request->bill_status)) {
            $billStatus = $request->bill_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('bill_status', $billStatusies);
        }
        if (!empty($request->current_step) && in_array($request->current_step, ['B', 'C'])) {
            $query->whereRaw('FIND_IN_SET(\'' . $request->current_step . '\',current_step)');
        } elseif (!empty($request->current_step)) {
            $query->where('current_step', $request->current_step);
        }


        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('setup_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('setup_date', $createAts);
        }
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $ProjectSerialNo 询价单流水号
     */
    public function getProjectNo($newNumber = null) {
        $prefix = 'ZB';
        $qurey = $this->model->selectRaw('*');
        $billNo = $newNumber ? $newNumber : $qurey
                        ->where('bill_no', 'like', $prefix . '%')
                        ->orderBy('bill_no', 'DESC')
                        ->value('bill_no');
        if (!empty($billNo)) {
            $date = substr($billNo, 2, 8);
            $serialSetp = substr($billNo, 10, 5);
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

    public function getBillStatusText($status) {
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

    public function getCurrentStepText($currentSteps) {
        $currentStepList = explode(',', $currentSteps);
        $currentStepArr = [];

        foreach ($currentStepList as $currentStep) {
            switch (strtoupper($currentStep)) {
                case '':
                    break;
                case 'A':
                    $currentStepArr[] = '招标立项';
                    break;
                case 'B':
                    $currentStepArr[] = '供方入围';
                    break;
                case 'C':
                    $currentStepArr[] = '标书编制';
                    break;
                case 'D':
                    $currentStepArr[] = '标底编制';
                    break;
                case 'E':
                    $currentStepArr[] = '招标交底';
                    break;
                case 'F':
                    $currentStepArr[] = '发标';
                    break;
                case 'G':
                    $currentStepArr[] = '答疑';
                    break;
                case 'H':
                    $currentStepArr[] = '开标';
                    break;
                case 'I':
                    $currentStepArr[] = '评标';
                    break;
                case 'J':
                    $currentStepArr[] = '商务谈判';
                    break;
                case 'K':
                    $currentStepArr[] = '定标';
                    break;
            }
        }
        return implode(' ', $currentStepArr);
    }

    /**
     * 招标范围
     * @param type $purMode
     * @return string
     */
    public function getDocTypeText($purMode) {
        switch (strtoupper($purMode)) {
            case '1':
                return '技术标+商务标';
            case '2':
                return '仅商务标';
        }
    }

    /**
     * 委托方式
     * @param type $purMode
     * @return string
     */
    public function getEntrustmentWay($purMode) {
        switch (strtoupper($purMode)) {
            case '1':
                return '不委托';
            case '2':
                return '委托代理机构';
            case '3':
                return '委托采购组织';
        }
    }

    /**
     * 开标方式
     * @param type $purMode
     * @return string
     */
    public function getOpenType($purMode) {
        switch (strtoupper($purMode)) {
            case '1':
                return '先开技术、后开商务';
            case '2':
                return '统一开标';
        }
    }

    /**
     * 评标方式
     * @param type $purMode
     * @return string
     */
    public function getEvaluatedMethod($purMode) {
        switch (strtoupper($purMode)) {
            case '1':
                return '定量评审';
            case '2':
                return '定性评审';
            case '3':
                return '定量+定性评审';
        }
    }

    /**
     * 评定标方法
     * @param type $purMode
     * @return string
     */
    public function getEvaluateDecide($purMode) {
        switch (strtoupper($purMode)) {
            case '1':
                return '综合评分法';
            case '2':
                return '合理低价法';
        }
    }

    /**
     * 评定标方法
     * @param type $purMode
     * @return string
     */
    public function getEvalTypeText($purMode) {
        switch (strtoupper($purMode)) {
            case '1':
                return '技术标';
            case '2':
                return '商务标';
            case '3':
                return '技术标+商务标';
        }
    }

    /**
     * 变更项目截止时间
     * @param Request $request
     */
    public function changeEnrollDate(Request $request) {
        $id = $request->id;
        $projectObj = Project::where('id', $id)->first();
        $sub = ProjectSub::where('project_id', $id)->first();
        $admin = Auth::guard('admin')->user();
        $orgId = $this->getPPurchaserId();
        if (empty($projectObj)) {
            check(false, '招标不存在');
        }
        $project = $projectObj->toArray();
        $projectData = $project;
        $data = [
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id,
        ];
        $subData = [
            'modify_time' => date('Y-m-d H:i:s'),
        ];
        if ($projectObj->bill_status !== 'C') {
            check(false, '招标不是已审核');
        }
        $time = date('Y-m-d H:i:s');
        if (!empty($data['bid_publish_date'])) {
            $data['bid_publish_date'] = date('Y-m-d', strtotime($data['bid_publish_date']));
        }
        if (!empty($data['bid_evaluation_date'])) {
            $data['bid_evaluation_date'] = date('Y-m-d', strtotime($data['bid_evaluation_date']));
        }
        if (!empty($data['bid_decision_date'])) {
            $data['bid_decision_date'] = date('Y-m-d', strtotime($data['bid_decision_date']));
        }
        if (!empty($data['approach_date'])) {
            $data['approach_date'] = date('Y-m-d', strtotime($data['approach_date']));
        }

        $currentStepList = explode(',', $projectObj->current_step);
        if (!empty($request->invitation_deadline) && in_array($projectObj->current_step, ['A', 'B', 'C', 'B,C']) && $project['supplier_invitation'] != '1') {
            if ($request->invitation_deadline != $project['enroll_deadline']) {
                check($request->invitation_deadline > $time, '投标报名截止时间须晚于当前时间');
                $data['enroll_deadline'] = date('Y-m-d H:i:s', strtotime($request->invitation_deadline));
                $subData['invitation_deadline'] = date('Y-m-d H:i:s', strtotime($request->invitation_deadline));
            }
        }
        if (!empty($data['enroll_deadline']) && $project['supplier_invitation'] != '1') {
            check($subData['invitation_deadline'] > $time, '投标报名截止时间须晚于当前时间');
        }
        if (!empty($data['enroll_deadline']) && $projectObj->bid_document == '1' && $project['supplier_invitation'] != '1') {
            $data['current_step'] = 'B';
            $data['shortlist_at'] = null;
            $data['supplier_invitation'] = 0;
        } elseif (!empty($data['enroll_deadline']) && $project['supplier_invitation'] != '1') {
            $data['current_step'] = 'B,C';
            $data['shortlist_at'] = null;
            $data['supplier_invitation'] = 0;
        }

        if (!empty($request->supplier_invi_end_date) && $project['supplier_invitation'] != '1') {
            $data['supplier_invi_end_date'] = date('Y-m-d H:i:s', strtotime($request->supplier_invi_end_date));
        }

        if (!empty($data['enroll_deadline']) && !empty($data['supplier_invi_end_date'])) {
            check($data['supplier_invi_end_date'] > $data['enroll_deadline'], '入围完成日期须晚于报名截止时间');
        } elseif (!empty($data['supplier_invi_end_date']) && empty($data['enroll_deadline']) && $project['supplier_invitation'] != '1') {
            check($data['supplier_invi_end_date'] > $projectObj->enroll_deadline, '入围完成日期须晚于报名截止时间');
            check($data['supplier_invi_end_date'] > $time, '入围完成日期须晚于当前时间');
        }

        if (!empty($request->commercial_doc_end_date) && $project['bid_document'] != '1') {
            $data['commercial_doc_end_date'] = date('Y-m-d H:i:s', strtotime($request->commercial_doc_end_date));
        }
        if (!empty($data['supplier_invi_end_date']) && !empty($data['commercial_doc_end_date'])) {
            check($data['commercial_doc_end_date'] >= $data['supplier_invi_end_date'], '商务标编制完成日期须晚于入围完成日期');
        } elseif (!empty($data['commercial_doc_end_date']) && empty($data['supplier_invi_end_date']) && $project['supplier_invitation'] != '1') {
            check($data['commercial_doc_end_date'] >= $projectObj->supplier_invi_end_date, '商务标编制完成日期须晚于入围完成日期');
            check($data['commercial_doc_end_date'] > $time, '商务标编制完成日期须晚于当前时间');
        }
        if (!empty($request->technical_doc_end_date) && $project['bid_document'] != '1') {
            $data['technical_doc_end_date'] = date('Y-m-d H:i:s', strtotime($request->technical_doc_end_date));
        }
        if (!empty($data['technical_doc_end_date']) && !empty($data['commercial_doc_end_date'])) {
            check($data['technical_doc_end_date'] >= $data['commercial_doc_end_date'], '技术标编制完成日期须晚于等于商务标编制完成日期');
        } elseif (!empty($data['technical_doc_end_date']) && empty($data['commercial_doc_end_date']) && $project['supplier_invitation'] != '1') {
            check($data['technical_doc_end_date'] >= $projectObj->commercial_doc_end_date, '技术标编制完成日期须晚于等于商务标编制完成日期');
            check($data['technical_doc_end_date'] > $time, '技术标编制完成日期须晚于当前时间');
        }


        if (!empty($request->bid_publish_date) && $project['bid_publish'] != '1') {
            $data['bid_publish_date'] = date('Y-m-d', strtotime($request->bid_publish_date));
        }
        if (!empty($data['bid_publish_date']) && !empty($subData['commercial_doc_end_date'])) {
            check($data['bid_publish_date'] . ' 23:59:59' > $subData['commercial_doc_end_date'], '发标日期须晚于标底编制完成日期');
        } elseif (!empty($data['clarificaiton_date']) && empty($subData['commercial_doc_end_date']) && $project['bid_document'] != '1') {
            check($data['bid_publish_date'] . ' 23:59:59' > $sub->commercial_doc_end_date, '发标日期须晚于标底编制完成日期');
            check($data['bid_publish_date'] . ' 23:59:59' > $time, '发标日期须晚于当前时间');
        }


        if (!empty($request->bid_open_deadline) && $project['bid_open'] != '1') {
            $data['bid_open_deadline'] = date('Y-m-d H:i:s', strtotime($request->bid_open_deadline));
        } elseif (!empty($request->bid_open_deadline) && $project['bid_open'] == '1' && $project['current_step'] == 'H') {
            $data['bid_open_deadline'] = date('Y-m-d H:i:s', strtotime($request->bid_open_deadline));
            if ($project['current_step'] == 'H') {
                $project['current_step'] = 'F';
            }
        }
        if (!empty($data['bid_open_deadline']) && !empty($data['bid_publish_date'])) {
            check($data['bid_open_deadline'] > $data['bid_publish_date'], '截标开标时间须晚于发标日期');
            if (!empty($data['commercial_doc_end_date']) && !empty($data['technical_doc_end_date']) && !empty($data['supplier_invi_end_date'])) {
                check($data['bid_open_deadline'] >= $data['commercial_doc_end_date'], '截标开标时间须须晚于商务标编制完成日期');
                check($data['bid_open_deadline'] >= $data['technical_doc_end_date'], '截标开标时间须晚于技术标编制完成日期');
                check($data['bid_open_deadline'] >= $data['supplier_invi_end_date'], '截标开标时间须晚于入围完成日期');
            } elseif (!empty($data['commercial_doc_end_date']) && !empty($data['supplier_invi_end_date'])) {
                check($data['bid_open_deadline'] >= $data['commercial_doc_end_date'], '截标开标时间须须晚于等于商务标编制完成日期');
                check($data['bid_open_deadline'] >= $data['supplier_invi_end_date'], '截标开标时间须晚于入围完成日期');
            } elseif (!empty($data['commercial_doc_end_date'])) {
                check($data['bid_open_deadline'] >= $data['commercial_doc_end_date'], '截标开标时间须须晚于等于商务标编制完成日期');
            }
        } elseif (!empty($data['bid_open_deadline']) && empty($data['bid_publish_date']) && $project['bid_open'] != '1') {
            check($data['bid_open_deadline'] > $projectObj->bid_publish_date, '截标开标时间须晚于发标日期');
            check($data['bid_open_deadline'] > $time, '截标开标时间须晚于当前时间');
        } elseif (!empty($data['bid_open_deadline']) && $project['bid_open'] == '1' && $project['current_step'] == 'H') {
            check($data['bid_open_deadline'] > $time, '截标开标时间须晚于当前时间');
        }




        if (!empty($request->bid_decision_date) && $project['bid_decision'] != '1') {
            $data['bid_decision_date'] = date('Y-m-d', strtotime($request->bid_decision_date));
        }
        if (!empty($data['bid_decision_date']) && !empty($data['bid_open_deadline'])) {
            check($data['bid_decision_date'] . ' 23:59:59' > $data['bid_open_deadline'], '定标日期须晚于截标开标时间');
        } elseif (!empty($data['bid_decision_date']) && empty($data['bid_open_deadline'])) {
            check($data['bid_decision_date'] . ' 23:59:59' > $projectObj->bid_open_deadline, '定标日期须晚于截标开标时间');
            check($data['bid_decision_date'] . ' 23:59:59' > $time, '定标日期须晚于当前时间');
        }

        if (!empty($request->approach_date) && $this->inArray($currentStepList, ['A', 'B', 'C', 'E', 'D', 'F', 'G', 'H', 'I', 'J', 'K'])) {
            $data['approach_date'] = date('Y-m-d', strtotime($request->approach_date));
        }
        if (!empty($data['approach_date']) && !empty($data['bid_decision_date'])) {
            check($data['approach_date'] . ' 23:59:59' > $data['bid_decision_date'], '进场日期须晚于定标日期');
        } elseif (!empty($data['approach_date']) && empty($data['bid_decision_date'])) {
            check($data['approach_date'] . ' 23:59:59' > $projectObj->bid_decision_date, '进场日期须晚于定标日期');
            check($data['approach_date'] . ' 23:59:59' > $time, '进场日期须晚于当前时间');
        }
        $flag = Project::where('id', $id)->update($data);
        ProjectSub::where('project_id', $id)->update($subData);
        if (empty($data['enroll_deadline'])) {
            return $flag;
        }
        $noticeId = NoticeSub::where('src_bill_id', $id)
                ->where('src_bill_type', 'sou_project')
                ->value('notice_id');
        if (empty($noticeId)) {
            return $flag;
        }
        $projectData['entrustment_way_name'] = $this->getEntrustmentWay($projectData['entrustment_way']);
        (new PurTypeRepo)->setPurType($projectData, 'pur_type_id', 'pur_type_name');
        (new UserRepo)->setUser($projectData, 'contact_id', 'contact_name');
        $projectData['enroll_deadline'] = $data['enroll_deadline'];
        $content = view('tpl.project_notice', $projectData)->toHtml();
        Notice::where('id', $noticeId)
                ->update([
                    'due_date' => $data['enroll_deadline'],
                    'content' => $content,
        ]);
        if ($project['bid_mode_id'] == 1) {
            return $flag;
        }
        $this->updateInvitation($id, $project['enroll_deadline'], $data['enroll_deadline']);
        return $flag;
    }

    public function updateInvitation($projectId, $oldEnrollDeadline, $enrollDeadline) {
        ProjectInvitation::where('project_id', $projectId)->update([
            'deadline_date' => $enrollDeadline,
        ]);
        DB::update('UPDATE project_invitation_entry,project_invitation SET'
                . ' project_invitation_entry.content=REPLACE(content,\'' . $oldEnrollDeadline . '\',\'' . $enrollDeadline . '\') '
                . 'where project_invitation_entry.invitation_id=project_invitation.id AND project_invitation.project_id=\'' . $projectId . '\'');
    }

    public function inArray($currentStepList, $currentStepArr) {

        foreach ($currentStepList as $currentStep) {
            if (empty($currentStep)) {
                continue;
            }
            if (in_array($currentStep, $currentStepArr)) {
                return true;
            }
        }
        return false;
    }

    public function notice($projectData) {
        $content = view('tpl.project_notice', $projectData)->toHtml();
        $data = [
            'biz_type' => 2,
            'due_date' => date('Y-m-d H:i:s', strtotime($projectData['enroll_deadline'])),
            'org_id' => $projectData['org_id'],
            'bill_no' => (new NoticeManageRepo)->getNoticeNo(),
            'src_bill_id' => $projectData['id'],
            'src_bill_type' => 'sou_project',
            'sup_scope' => $projectData['bid_mode_id'],
            'bill_status' => 'C',
            'bill_type_id' => 0,
            'src_bill_no' => '招标立项：' . $projectData['bill_no'],
            'title' => $projectData['name'] . '-招标公告',
        ];
        $data['content'] = $content;
        return $data;
    }

    public function getProcess($projectId) {
        $projectObj = Project::where('id', $projectId)->first();

        if (empty($projectObj)) {
            check(false, '招标不存在');
        }
        $project = $projectObj->toArray();
        $setupDate = $project['setup_date'];
        $currentStep = $project['current_step'];
        $active = $projectObj->bill_status == 'A' ? 'Ongoing' : 'Done';
        $currentSteps = explode(',', $currentStep);
        $currentStepArr = [];
        foreach ($currentSteps as $current_step) {
            if (!empty($current_step)) {
                $currentStepArr[] = ord($current_step);
            }
        }
        $invitationActive = 'Unstart';
        if (in_array('B', $currentSteps)) {
            $invitationActive = 'Ongoing';
        } elseif ($project['supplier_invitation'] == '1') {
            $invitationActive = 'Done';
        }
        $documentAt = null;
        $documentActive = 'Unstart';
        if (in_array('C', $currentSteps)) {
            $documentActive = 'Ongoing';
        } elseif ($project['bid_document'] == '1') {
            $documentObj = ProjectDocument::where('project_id', $projectId)->selectRaw('document_at,bill_status')->first();
            $documentAt = !empty($documentObj) ? $documentObj->document_at : null;
            $documentActive = 'Done';
        }

        $publishAt = null;
        $publishActive = 'Unstart';
        if (in_array('F', $currentSteps) && $project['bid_publish'] != '1') {
            $publishActive = 'Ongoing';
        } elseif ($project['bid_publish'] == '1') {
            $publishObj = ProjectPublish::where('project_id', $projectId)->selectRaw('publish_at,publish_status')->first();
            $publishActive = 'Done';
            $publishAt = !empty($publishObj) ? $publishObj->publish_at : null;
        }
        $openAt = null;
        $openActive = 'Unstart';
        if (in_array('H', $currentSteps) && $project['bid_open'] != '1') {
            $openActive = 'Ongoing';
        } elseif ($project['bid_open'] == '1') {
            $openObj = \App\Common\Models\Project\ProjectOpen::where('project_id', $projectId)->selectRaw('open_at,open_status')->first();
            $openActive = 'Done';
            $openAt = !empty($openObj) ? $openObj->open_at : null;
        }


        $decisionAt = null;
        $decisionActive = 'Unstart';
        if ($project['bid_decision'] != '1' && in_array('K', $currentSteps)) {
            $decisionActive = 'Ongoing';
        } elseif ($project['bid_decision'] == '1') {
            $decisionObj = ProjectDecision::where('project_id', $projectId)->selectRaw('decision_at,decision_status')->first();
            $decisionAt = !empty($decisionObj) ? $decisionObj->decision_at : null;
            $decisionActive = !empty($decisionObj) && $decisionObj->decision_status === 'C' ? 'Done' : 'Ongoing';
        }
        return [
            'setup' => [
                'real_at' => $setupDate,
                'active' => $active,
                'estimate_at' => $setupDate,
            ],
            'invitation' => [
                'real_at' => $project['shortlist_at'],
                'active' => $invitationActive,
                'estimate_at' => $project['supplier_invi_end_date'],
            ],
            'document' => [
                'real_at' => $documentAt,
                'active' => $documentActive,
                'estimate_at' => !empty($project['technical_doc_end_date']) ? $project['technical_doc_end_date'] : $project['commercial_doc_end_date'],
            ],
            'publish' => [
                'real_at' => $publishAt,
                'active' => $publishActive,
                'estimate_at' => !empty($project['bid_publish_date']) ? substr($project['bid_publish_date'], 0, 10) : '',
            ],
            'open' => [
                'real_at' => $openAt,
                'active' => $openActive,
                'estimate_at' => $project['bid_open_deadline'],
            ],
            'decision' => [
                'real_at' => $decisionAt,
                'active' => $decisionActive,
                'estimate_at' => !empty($project['bid_decision_date']) ? substr($project['bid_decision_date'], 0, 10) : ''
            ],
            'current_step' => $currentStepArr
        ];
    }

}
