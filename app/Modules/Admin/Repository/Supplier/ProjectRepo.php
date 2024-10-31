<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Message,
    MessageReceiver,
    Project\Attach As ProjectAttach,
    Project\Attach,
    Project\Project,
    Project\ProjectBidQuote,
    Project\ProjectDecision,
    Project\ProjectDocument,
    Project\ProjectPay,
    Project\ProjectPublish,
    Project\ProjectSub,
    Project\ProjectSupplier,
    Project\ProjectSupplierDownload,
    Project\ProjectThanks,
    Project\ProjectThanksEntry,
    Supplier AS BaseSupplier,
    UserSupplier
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    BidModeRepo,
    Project\AttachRepo,
    Project\ProjectBidAttachRepo,
    Project\ProjectBidEntryRepo,
    Project\ProjectEntryRepo,
    Project\ProjectMemberRepo,
    Project\ProjectPublishFileRepo,
    Project\ProjectSubRepo,
    Project\ProjectSupplierRepo,
    PurTypeRepo,
    Supplier\NoticeRepo,
    Supplier\ProjectInvitationRepo,
    UserRepo,
    ValuationModeRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectRepo extends Repository {

    protected $model;
    protected $sorts = [
    ];
    protected $supplierId = null;
    protected $source = null;
    protected $supplierName = null;
    protected $admin = null;
    protected $userId = null;
    protected $filter = [
        'statistics',
        'todo',
    ];

    public function __construct() {
        $this->model = new Project();
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
        $supplier = BaseSupplier::where('id', $this->supplierId)
                ->selectRaw('id,status,deleted_flag,enable,source,name')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        if (!in_array($action, $this->filter)) {
            check($supplier->status === 'APPROVED', '供应商没有准入');
        }
        check($supplier->enable == '1', '供应商已被禁用');
        $this->source = $supplier->source;
        $this->supplierName = $supplier->name;
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

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'setup_date';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'setup_date') {
            $query->orderBy('setup_date', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request) {
        $supplierTable = (new ProjectSupplier)->getTable();
        $projectSubTable = (new ProjectSub)->getTable();
        $supplierId = $this->supplierId;
        $filed = 'bb.id,bb.bill_no,bb.name,bb.created_by,bb.current_step,bb.bill_status,bb.setup_date,bs.is_tender,bs.tended_at,bs.tended_by,'
                . 'ps.invitation_deadline as enroll_deadline,bs.status,bs.enroll_status,bb.bid_mode_id,bb.org_id,bs.enroll_at,bs.pay_flag';
        $query = $this->model
                ->from($this->model->getTable() . ' as bb')
                ->selectRaw($filed)
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
                    $join->on('bb.id', '=', 'bs.project_id')
                    ->where('bs.supplier_id', $supplierId);
                })
                ->join($projectSubTable . ' As ps', function($join) {
            $join->on('ps.project_id', '=', 'bb.id');
        });
        $this->getWhere($query, $request);
        $query->where(function($q) {
                    $q->whereRaw('(bs.id IS NOT NULL AND bid_mode_id!=1)')
                    ->orWhere(function($q1) {
                        $q1->where('bid_mode_id', 1);
                    });
                })
                ->whereIn('bb.bill_status', ['C', 'X']);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        $time = date('Y-m-d H:i:s');
        foreach ($data as &$item) {
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            if ($item['enroll_at'] > $time && ((empty($item['status']) && $item['bid_mode_id'] == '1') || $item['status'] === 'T' )) {
                $item['status'] = 'Y';
                $item['status_name'] = '已报名';
            } elseif ($item['enroll_at'] < $time && ((empty($item['status']) && $item['bid_mode_id'] == '1') || $item['status'] === 'T' )) {
                $item['status'] = 'WCY';
                $item['status_name'] = '待报名';
            } else {
                $item['status_name'] = $this->getStatusName($item['status']);
            }
            if (empty($item['enroll_status'])) {
                $item['enroll_status'] = 'WCY';
            }
            $item['access_status'] = 'APPROVED';
            $item['current_step_name'] = $this->getCurrentStepText($item['current_step']);
            if ($item['bill_status'] == 'X') {
                $item['select_status_name'] = '已流标';
            } elseif ($item['enroll_deadline'] > $time) {
                $item['select_status_name'] = '报名中';
            } elseif ($item['enroll_deadline'] < $time) {
                $item['select_status_name'] = '已截止';
            }
        }
        (new BidModeRepo)->setBidModes($data, 'bid_mode_id', 'bid_mode_name');
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'tended_by', 'tended_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function getStatusName($status) {

        switch (strtoupper($status)) {
            case 'X':
                return '已流标';
            case 'N':
                return '不报名';
            case 'Y':
                return '已报名';
            case 'WCY':
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

    public function getTotal(Request $request) {
        $supplierTable = (new ProjectSupplier)->getTable();
        $supplierId = $this->supplierId;
        $query = $this->model
                ->from($this->model->getTable() . ' as bb')
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
            $join->on('bb.id', '=', 'bs.project_id')
            ->where('bs.supplier_id', $supplierId);
        });

        $this->getWhere($query, $request);
        $query->whereRaw('bs.id IS NOT NULL AND bs.`status`  IN(\'I\',\'F\',\'G\')')
                ->whereIn('bb.bill_status', ['C']);
        return $query->count();
    }

    /**
     * 待缴费的竞价
     * @param Request $request
     * @return type
     */
    public function todoHandeling(Request $request) {
        $supplierTable = (new ProjectSupplier)->getTable();
        $projectSubTable = (new ProjectSub)->getTable();
        $supplierId = $this->supplierId;
        $query = $this->model
                        ->from($this->model->getTable() . ' as bb')
                        ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
                            $join->on('bb.id', '=', 'bs.project_id')
                            ->where('bs.supplier_id', $supplierId);
                        })->join($projectSubTable . ' As ps', function($join) {
            $join->on('ps.project_id', '=', 'bb.id');
        });
        $this->getWhere($query, $request);
        $query->where(function($q) {
                    $q->whereRaw('(bs.id IS NOT NULL AND bid_mode_id=2)')
                    ->orWhere(function($q1) {
                        $q1->where('bid_mode_id', 1);
                    });
                })
                ->where('bb.bill_status', 'C')
                ->whereRaw("((bs.enroll_status IS NULL or bs.enroll_status ='WCY' ) AND ps.invitation_deadline>=NOW())");
        return $query->count();
    }

    /**
     * 待缴费的竞价
     * @param Request $request
     * @return type
     */
    public function todoBid(Request $request) {
        $supplierTable = (new ProjectSupplier)->getTable();
        $supplierId = $this->supplierId;
        $query = $this->model
                ->from($this->model->getTable() . ' as bb')
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
            $join->on('bb.id', '=', 'bs.project_id')
            ->where('bs.supplier_id', $supplierId);
        });
        $query->where('bs.status', 'K');
        $query->whereIn('bb.bill_status', ['C']);
        return $query->count();
    }

    /**
     * 待缴费的竞价
     * @param Request $request
     * @return type
     */
    public function todoPay(Request $request) {
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
        });
        $query->where('s.supplier_id', $this->supplierId);
        $query->where('ps.bill_status', 'A');
        return $query->count();
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function noticeInfo($id) {
        $supplierTable = (new ProjectSupplier)->getTable();
        $supplierId = $this->supplierId;
        $query = $this->model->from($this->model->getTable() . ' as bb')
                ->selectRaw('bb.bid_mode_id,bs.enroll_status,bb.name,bb.org_id')
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
            $join->on('bb.id', '=', 'bs.project_id')
            ->where('bs.supplier_id', $supplierId);
        });
        $query->where('bb.id', $id);
        $object = $query->first();
        $sub = ProjectSub::where('project_id', $id)->first();
        if (empty($object)) {
            return [];
        }

        switch ($object->bid_mode_id) {
            case '1':
                $data = (new NoticeRepo)->projectInfo($id);
                break;
            case '2':
                $data = (new ProjectInvitationRepo)->info($id);
                $data['org_id'] = $object->org_id;
                break;
        }
        $data['bid_mode_id'] = $object->bid_mode_id;
        $data['invitation_deadline'] = $sub->invitation_deadline;
        if (empty($object->enroll_status)) {
            $data['enroll_status'] = 'WCY';
        } else {
            $data['enroll_status'] = $object->enroll_status;
        }
        $data['id'] = $id;
        (new BidModeRepo)->setBidMode($data, 'bid_mode_id', 'bid_mode_name');
        $data['access_status'] = 'APPROVED';

        if ($object->bid_mode_id == '1' && empty($data['name'])) {
            $data['name'] = '【' . $object->name . '】公开招标';
        }
        return $data;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function cmfInfo($id) {
        $supplierTable = (new ProjectSupplier)->getTable();
        $supplierId = $this->supplierId;
        $query = $this->model->from($this->model->getTable() . ' as bb')
                ->selectRaw('bb.bid_mode_id,bs.status,bb.name,bb.org_id')
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
            $join->on('bb.id', '=', 'bs.project_id')
            ->where('bs.supplier_id', $supplierId);
        });
        $query->where('bb.id', $id);
        $object = $query->first();
        $sub = ProjectSub::where('project_id', $id)->first();
        if (empty($object)) {
            return [];
        }
        switch ($object->status) {
            case 'F':
                $data = (new NoticeRepo)->projectInfo($id, '5', ['1', '2']);
                break;
            case 'G':
                $data = (new ProjectThanksRepo)->info($id);
                $data['org_id'] = $object->org_id;
                break;
        }
        $data['bid_mode_id'] = $object->bid_mode_id;
        $data['invitation_deadline'] = $sub->invitation_deadline;
        $data['enroll_status'] = $object->enroll_status;
        $data['id'] = $id;
        (new BidModeRepo)->setBidMode($data, 'bid_mode_id', 'bid_mode_name');
        $data['access_status'] = 'APPROVED';

        if ($object->bid_mode_id == '1' && empty($data['name'])) {
            $data['name'] = '【' . $object->name . '】公开招标';
        }
        return $data;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {
        $supplierTable = (new ProjectSupplier)->getTable();
        $supplierId = $this->supplierId;
        $query = $this->model->from($this->model->getTable() . ' as bb')
                ->selectRaw('bb.*,bs.enroll_at,bs.status,bs.enroll_status,bs.enroll_at,bb.org_id')
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
            $join->on('bb.id', '=', 'bs.project_id')
            ->where('bs.supplier_id', $supplierId);
        });
        $query->where('bb.id', $id);
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
        $data = [];

        $base['access_status'] = 'APPROVED';

        $data['base'] = $base;
        $sub = (new ProjectSubRepo)->info($id);
        if (!empty($sub) && $base) {
            unset($sub['id']);
            $data['base'] = array_merge($base, $sub);
        }

        $data['attach'] = (new AttachRepo)->getList($id);
        $data['member'] = (new ProjectMemberRepo)->getList($id);
        $data['entry'] = (new ProjectEntryRepo)->getList($id);
        $data['supplier'] = (new ProjectSupplierRepo)->getList($id, $supplierId, null, $base['org_id']);

        switch ($object->status) {
            case 'F':
                $data['notice'] = (new NoticeRepo)->projectInfo($id, '5', ['1', '2']);
                $data['notice']['bid_mode_name'] = $base['bid_mode_name'];
                break;
            case 'G':
            case 'H':
            case 'X':
            case 'Y':
            case 'E':
            case 'J':
            case 'K':
            case 'I':
            case 'H':
                $data['notice'] = $this->thinks($id);
                $data['notice']['org_id'] = $object->org_id;
                break;
        }
        $data['Process'] = $this->getProcess($id);
        $data['publishFile'] = (new ProjectPublishFileRepo)->getList($id);
        return $data;
    }

    public function publishDownload(int $projectId) {
        $publishObj = ProjectPublish::where('project_id', $projectId)->first();
        if (empty($publishObj) || $publishObj->publish_status != 'C') {
            check(false, '招标项目还未发标');
        }
        $qurey = Attach::selectRaw('*');
        $qurey->where('project_id', $projectId)
                ->whereIn('group', ['PUBLISH_DOWNLOAD']);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->first();
        if (empty($object)) {
            return [];
        }
        $base = $object->toArray();
        ProjectSupplier::where('project_id', $projectId)
                ->where('supplier_id', $this->supplierId)
                ->update(['doc_download_flag' => 'Y']);
        ProjectSupplierDownload::insert([
            'group' => 'PUBLISH_DOWNLOAD',
            'project_id' => $projectId,
            'supplier_id' => $this->supplierId,
            'created_by' => $this->userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $base;
    }

    public function docDownload(string $group, int $projectId, Request $request) {
        $publishObj = ProjectPublish::where('project_id', $projectId)->first();
        if (empty($publishObj) || $publishObj->publish_status != 'C') {
            check(false, '招标项目还未发标');
        }
        $docType = ProjectSub::where('project_id', $projectId)->value('doc_type');
        $docId = $request->doc_id;
        switch ($docType) {
            case '2':
                ProjectSupplierDownload::insert([
                    'group' => $group,
                    'project_id' => $projectId,
                    'doc_id' => !empty($docId) ? $docId : null,
                    'supplier_id' => $this->supplierId,
                    'created_by' => $this->userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                empty($docId) ? ProjectSupplier::where('project_id', $projectId)
                                        ->where('supplier_id', $this->supplierId)
                                        ->update(['doc_download_flag' => $group == 'COMMERCIAL' ? 'Y' : 'N']) : $this->setDownloadFlag($projectId);
                return true;
            case '1':
                ProjectSupplierDownload::insert([
                    'group' => $group,
                    'project_id' => $projectId,
                    'supplier_id' => $this->supplierId,
                    'doc_id' => !empty($docId) ? $docId : null,
                    'created_by' => $this->userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $this->setDownloadFlag($projectId);
        }
        return true;
    }

    public function thinks(int $project_id) {
        $supplierId = $this->supplierId;
        $thinkTable = (new ProjectThanks)->getTable();
        $thinkEntryTable = (new ProjectThanksEntry)->getTable();
        $query = ProjectThanks::from($thinkTable . ' as t')
                ->selectRaw('te.*,t.bid_mode_id,t.org_id,t.project_id,t.name,t.enable')
                ->leftJoin($thinkEntryTable . ' as te', function($join)use($supplierId) {
            $join->on('t.id', '=', 'te.thanks_id')
            ->where('te.supplier_id', $supplierId);
        });
        $query->where('t.project_id', $project_id);
        $query->where('t.status', 'C');
        $query->where('t.enable', '1');
        $query->where('te.supplier_id', $supplierId);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $base = $object->toArray();
        (new BidModeRepo)->setBidMode($base, 'bid_mode_id', 'bid_mode_name');
        return $base;
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
        if (!empty($request->status)) {
            $query->where('bs.status', $request->status);
        }
        if (!empty($request->enroll_status)) {
            $enroll_status = trim($request->enroll_status);
            if ($enroll_status == 'WCY') {
                $query->where(function ($q)use($enroll_status) {
                    $q->whereRaw("((bs.enroll_status IS NULL or bs.enroll_status ='WCY' ) AND ps.invitation_deadline>=NOW())");
                });
            } else {
                $query->where('bs.enroll_status', $enroll_status);
            }
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

    public function getBillStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '保存';
            case 'B':
                return '已提交';
            case 'C':
                return '已提交';
        }
    }

    /**
     * 采购品类
     * @param type $status
     * @return string
     */
    public function getCategoryText($status) {
        switch (strtoupper($status)) {
            case '1':
                return '新产品类';
            case '2':
                return '年度采购类';
            case '3':
                return '钢材类';
            case '4':
                return '外购产品类';
            case '5':
                return '外协产品类';
            case '6':
                return '耗材类';
            case '7':
                return '设备/服务类';
        }
    }

    /**
     * 证照要求
     * @param type $enterpriseType
     * @return string
     */
    public function getCertificateText($enterpriseType) {
        switch (strtoupper($enterpriseType)) {
            case '1':
                return '三/五证合一';
            case '2':
                return '营业执照';
            case '3':
                return '税务登记证';
            case '4':
                return '组织机构代码证';
            case '5':
                return '社会保险登记证';
            case '6':
                return '一般纳税人证明材料';
            case '7':
                return '统计登记证';
            case '8':
                return '其他证照';
        }
    }

    /**
     * 证照要求
     * @param type $taxType
     * @return string
     */
    public function getTaxTypeText($taxType) {
        switch (strtoupper($taxType)) {
            case '1':
                return '价外税(含税)';
            case '2':
                return '价外税(不含税)';
            case '3':
                return '价内税(含税)';
        }
    }

    public function getInvtypeText($invtype) {
        switch (strtoupper($invtype)) {
            case '1':
                return '普通电子发票';
            case '2':
                return '电子发票专票';
            case '3':
                return '普通纸质发票';
            case '4':
                return '专用纸质发票';
            case '5':
                return '普通纸质卷票';
            case '6':
                return '增值税专用发票';
            case '7':
                return '增值税普通发票';
            case '9':
                return '不需要发票';
        }
    }

    public function getBizModelText($bizModel) {
        switch (strtoupper($bizModel)) {
            case '1':
                return '生产加工';
            case '2':
                return '经销批发';
            case '3':
                return '商业服务';
            case '4':
                return '招商代理';
        }
    }

    /**
     * 发起方
     * @param type $origin
     * @return string
     */
    public function getOriginText($origin) {
        switch (strtoupper($origin)) {
            case '1':
                return '供应商';
            case '2':
                return '采购方';
        }
    }

    /**
     * 确认状态
     * @param type $cfmStatus
     * @return string
     */
    public function getCfmStatusText($cfmStatus) {
        switch (strtoupper($cfmStatus)) {
            case 'A':
                return '待确认';
            case 'B':
                return '已确认';
            case 'C':
                return '已打回';
        }
    }

    /**
     * 确认状态
     * @param type $cfmStatus
     * @return string
     */
    public function getComboFieldText($cfmStatus) {
        switch (strtoupper($cfmStatus)) {
            case '1':
                return '正常定价';
            case '2':
                return '异常定价';
        }
    }

    /**
     * 报名参加竞价
     * @param int $id
     * @param Request $request
     */
    public function signUp(int $id, Request $request) {
        $supplierId = $this->supplierId;
        $admin = $this->admin;
        $project = Project::where('id', $id)
                ->select('bill_status', 'current_step', 'name', 'org_id', 'id', 'email', 'contact_id', 'org_id')
                ->first();
        $project_sub = ProjectSub::where('project_id', $id)
                ->select('invitation_deadline')
                ->first();

        $supplier = BaseSupplier::where('id', $supplierId)
                ->selectRaw('id,status,deleted_flag,enable,source')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        check($supplier->status === 'APPROVED', '供应商没有通过审核');
        check($supplier->enable == '1', '供应商已被禁用');
        if ($project_sub->invitation_deadline < date('Y-m-d H:i:s')) {
            check(false, '已过报名截止时间');
        }
        if ($project->bill_status != 'C') {
            check(false, '单据状态状态不是已审核');
        }
        $currentStepList = explode(',', $project->current_step);
        /* if (!in_array('A', $currentStepList)) {
          check(false, '项目状态不是报名中');
          } */
        $isupplier = ProjectSupplier::where('project_id', $id)
                ->where('supplier_id', $supplierId)
                ->first();
        if (!empty($isupplier) && $isupplier->enroll_status == 'Y') {
            check(false, '已经报名请勿重复报名');
        }
        if ($isupplier) {
            $isupplierId = $isupplier->id;
        } else {
            $isupplierId = '';
            $projectSub = ProjectSub::where('project_id', $id)
                    ->select('tender_fee', 'deposit')
                    ->first();
        }
        $flag = empty($isupplierId) ? ProjectSupplier::insert([
                    'supplier_id' => $supplierId,
                    'enroll_at' => date('Y-m-d H:i:s'),
                    'project_id' => $id,
                    'enroll_status' => 'Y',
                    'invitation_status' => 'Y',
                    'status' => 'Y',
                    'enroll_id' => $this->userId,
                    'created_by' => $this->userId,
                    'note' => $request->note,
                    'tender_fee' => $projectSub->tender_fee,
                    'supplier_deposit' => $projectSub->deposit,
                    'supplier_name' => $request->supplier_name,
                    'supplier_contact' => $request->real_name,
                    'contact_phone' => $request->phone,
                    'contact_email' => $request->email,
                    'created_at' => date('Y-m-d H:i:s')
                ]) : ProjectSupplier::where('id', $isupplierId)->update([
                    'enroll_status' => 'Y',
                    'invitation_status' => 'Y',
                    'enroll_at' => date('Y-m-d H:i:s'),
                    'status' => 'Y',
                    'note' => $request->note,
                    // 'remark' => $request->note,
                    'enroll_id' => $this->userId,
                    'supplier_name' => $request->supplier_name,
                    'supplier_contact' => $request->real_name,
                    'contact_phone' => $request->phone,
                    'contact_email' => $request->email,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->userId,
        ]);
        $this->sendSigns($id);
        return $flag;
    }

    public function sendSigns($projectId) {
        $supplierId = $this->supplierId;
        $supplier = ProjectSupplier::where('project_id', $projectId)
                ->where('supplier_id', $supplierId)
                ->first();
        $project = Project::where('id', $projectId)
                ->select('bill_status', 'current_step', 'name', 'org_id', 'id', 'email', 'contact_id', 'org_id')
                ->first();
        if ($supplier['enroll_status'] !== 'Y') {
            return;
        }
        $this->sendSignMail($project, $project['email']);
        $this->sendSignMessage($project);
    }

    public function sendSignMessage($projectData) {
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'PURCHASER',
                    'content_url' => $bossUrl . '/front/#/inviteTenders/ProjectApprovalDetails?id=' . $projectData['id'],
                    'sender_id' => $projectData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . env('APP_NAME') . '】【' . $projectData['name'] . '】【' . $this->supplierName . '】已报名',
                    'message' => '您好，【' . $projectData['name'] . '】已有供应商报名成功，请尽快登录系统查看报名信息。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $data = [
            'message_id' => $messageId,
            'receiver_id' => $projectData['contact_id'],
            'supplier_id' => $this->supplierId,
            'org_id' => $projectData['org_id'],
            'read_flag' => 'N',
            'created_at' => date('Y-m-d H:i:s')
        ];
        MessageReceiver::insert($data);
    }

    public function sendSignMail($projectData, $email) {
        if (!empty($email)) {
            //$sent = new SendMailJob(['email' => $email, 'projectData' => $projectData, 'supplierName' => $this->supplierName], 'PROJECT_SIGN');
            //$sent->handle();
            app(Dispatcher::class)->dispatch
                    (new SendMailJob([
                'projectData' => $projectData,
                'email' => $email,
                'supplierName' => $this->supplierName,
                    ], 'PROJECT_SIGN'));
        }
    }

    /**
     * 不报名参加竞价
     * @param int $id
     * @param Request $request
     */
    public function unSignUp(int $id, Request $request) {
        $supplierId = $this->supplierId;
        $admin = $this->admin;
        $project = Project::where('id', $id)
                ->select('enroll_deadline', 'bill_status', 'current_step', 'name', 'org_id', 'id')
                ->first();
        $project_sub = ProjectSub::where('project_id', $id)
                ->select('invitation_deadline')
                ->first();
        $supplier = BaseSupplier::where('id', $supplierId)
                ->selectRaw('id,status,deleted_flag,enable,source')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        check($supplier->status === 'APPROVED', '供应商没有准入');
        check($supplier->enable == '1', '供应商已被禁用');

        if ($project_sub->invitation_deadline < date('Y-m-d H:i:s')) {
            check(false, '已过报名截止时间');
        }
        if ($project->bill_status != 'C') {
            check(false, '单据状态状态不是已审核');
        }
        $currentStepList = explode(',', $project->current_step);

        $isupplier = ProjectSupplier::where('project_id', $id)
                ->where('supplier_id', $supplierId)
                ->first();
        if (!empty($isupplier) && $isupplier->enroll_status == 'N') {
            check(false, '已是不报名状态');
        }
        if ($isupplier) {
            $isupplierId = $isupplier['id'];
        } else {
            $isupplierId = '';
        }
        $flag = empty($isupplierId) ? ProjectSupplier::insert([
                    'supplier_id' => $supplierId,
                    'enroll_at' => date('Y-m-d H:i:s'),
                    'project_id' => $id,
                    'enroll_status' => 'N',
                    'invitation_status' => 'N',
                    'status' => 'N',
                    'enroll_id' => $this->userId,
                    'note' => $request->note,
                    //'remark' => $request->note,
                    'supplier_contact' => $admin->realname,
                    'supplier_name' => $this->supplierName,
                    'contact_phone' => $admin->phone,
                    'contact_email' => $admin->email,
                    'created_by' => $this->userId,
                    'created_at' => date('Y-m-d H:i:s')
                ]) : ProjectSupplier::where('id', $isupplierId)->update([
                    'enroll_status' => 'N',
                    'invitation_status' => 'N',
                    'enroll_at' => date('Y-m-d H:i:s'),
                    'status' => 'N',
                    'note' => $request->note,
                    // 'remark' => $request->note,
                    'enroll_id' => $this->userId,
                    'supplier_contact' => $admin->realname,
                    'supplier_name' => $this->supplierName,
                    'contact_phone' => $admin->phone,
                    'contact_email' => $admin->email,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->userId,
        ]);
        if ($project['bid_mode_id'] != 1) {
            $this->sendUnSigns($id);
        }
        return $flag;
    }

    public function sendUnSigns($projectId) {
        $supplierId = $this->supplierId;
        $supplier = ProjectSupplier::where('project_id', $projectId)
                ->where('supplier_id', $supplierId)
                ->first();
        $project = Project::where('id', $projectId)
                ->select('bill_status', 'current_step', 'name', 'org_id', 'id', 'email', 'contact_id', 'org_id')
                ->first();
        if ($supplier['enroll_status'] !== 'N') {
            return;
        }
        $this->sendUnSignMail($project, $project['email']);
        $this->sendUnSignMessage($project);
    }

    public function sendUnSignMessage($projectData) {
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'PURCHASER',
                    'content_url' => $bossUrl . '/front/#/inviteTenders/ProjectApprovalDetails?id=' . $projectData['id'],
                    'sender_id' => $projectData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . env('APP_NAME') . '】【' . $projectData['name'] . '】【' . $this->supplierName . '】不报名',
                    'message' => '您好，【' . $projectData['name'] . '】的供应商不报名，请尽快登录系统查看报名信息。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $data = [
            'message_id' => $messageId,
            'receiver_id' => $projectData['contact_id'],
            'supplier_id' => $this->supplierId,
            'org_id' => $projectData['org_id'],
            'read_flag' => 'N',
            'created_at' => date('Y-m-d H:i:s')
        ];
        MessageReceiver::insert($data);
    }

    public function sendUnSignMail($projectData, $email) {
        if (!empty($email)) {
            app(Dispatcher::class)->dispatch
                    (new SendMailJob([
                'projectData' => $projectData,
                'email' => $email,
                'supplierName' => $this->supplierName,
                    ], 'PROJECT_UNSIGN'));
        }
    }

    /**
     * 投标
     * @param int $id
     * @param Request $request
     */
    public function quote(int $id, Request $request) {
        $supplierId = $this->supplierId;
        $project = Project::where('id', $id)->first();
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $userId = $admin->user_id;
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $supplier = BaseSupplier::where('id', $supplierId)
                ->selectRaw('id,status,deleted_flag,enable,source')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        check($supplier->status === 'APPROVED', '供应商没有准入');
        check($supplier->enable == '1', '供应商已被禁用');
        $projectSupplier = ProjectSupplier::where('project_id', $id)
                ->where('supplier_id', $supplierId)
                ->first();
        if (!empty($projectSupplier) && $projectSupplier->shortlist_flag != 'Y') {
            check(false, '供应商未入围');
        }
        $projectSubObj = ProjectSub::where('project_id', $id)
                ->selectRaw('tender_fee,deposit,is_deposit,deposit_stage')
                ->first();
        check(!empty($projectSubObj), '招标不存在');
        $sub = $projectSubObj->toArray();
        $deposit = $sub['is_deposit'] == '1' ? $projectSupplier->supplier_deposit : $sub['deposit'];
        $base = $request->base;
        if ($sub['tender_fee'] == '0' && ($sub['deposit_stage'] == 2 || ($sub['deposit_stage'] == 1 && $deposit == 0))) {
            
        } elseif (!empty($projectSupplier) && $projectSupplier->pay_flag != 'Y' && $base['bid_status'] === 'C') {
            check(false, '供应商未完成缴费确认');
        }
        if (empty($project)) {
            check(false, '招标单不存在');
        }
        $currentStepList = explode(',', $project->current_step);
        if (!in_array('F', $currentStepList)) {
            check(false, '当前不是发标状态');
        }
        $quote = ProjectBidQuote::where('project_id', $base['project_id'])
                ->selectRaw('bid_status,supplier_id,id')
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->first();
        check(empty($quote), '投标已存在');
        $quoteData = [
            'project_id' => $base['project_id'],
            'supplier_id' => $supplierId,
            'inclu_tax_amount' => !empty($base['inclu_tax_amount']) ? $base['inclu_tax_amount'] : '',
            'tax_amount' => !empty($base['tax_amount']) ? $base['tax_amount'] : '',
            'except_tax_amount' => !empty($base['except_tax_amount']) ? $base['except_tax_amount'] : '',
            'project_manager' => !empty($base['project_manager']) ? $base['project_manager'] : '',
            'work_load' => !empty($base['work_load']) ? $base['work_load'] : '',
            'bid_status' => !empty($base['bid_status']) ? $base['bid_status'] : '',
            'comment' => !empty($base['comment']) ? $base['comment'] : '',
            'tended_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
        ];
        $quoteId = ProjectBidQuote::insertGetId($quoteData);
        (new ProjectBidAttachRepo)->updateData($quoteId, $request);
        (new ProjectBidEntryRepo)->updateData($quoteId, $request);
        if ($quoteData['bid_status'] === 'C') {
            ProjectSupplier::where('project_id', $base['project_id'])
                    ->where('supplier_id', $supplierId)
                    ->update([
                        'status' => 'I',
                        'is_tender' => '1',
                        'tended_by' => $admin->user_id,
                        'tended_at' => date('Y-m-d H:i:s')
            ]);
            $this->sendQuote($id);
        }
        return (string) $quoteId;
    }

    public function sendQuote($projectId) {
        $supplierId = $this->supplierId;
        $supplier = ProjectSupplier::where('project_id', $projectId)
                ->where('supplier_id', $supplierId)
                ->first();
        $project = Project::where('id', $projectId)
                ->select('bill_status', 'current_step', 'name', 'org_id', 'id', 'email', 'contact_id', 'org_id')
                ->first();
        if (!empty($supplier) && $supplier->enroll_status !== 'N') {
            return;
        }
        $this->sendQuoteMail($project, $project['email']);
        $this->sendQuoteMessage($project);
    }

    public function sendQuoteMessage($projectData) {
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'PURCHASER',
                    'content_url' => $bossUrl . '/front/#/inviteTenders/ProjectApprovalDetails?id=' . $projectData['id'],
                    'sender_id' => $projectData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . env('APP_NAME') . '】【' . $projectData['name'] . '】【' . $this->supplierName . '】已投标',
                    'message' => '您好，【' . $projectData['name'] . '】的【' . $this->supplierName . '】已投标，请尽快登录系统查看报名信息。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $data = [
            'message_id' => $messageId,
            'receiver_id' => $projectData['contact_id'],
            'supplier_id' => $this->supplierId,
            'org_id' => $projectData['org_id'],
            'read_flag' => 'N',
            'created_at' => date('Y-m-d H:i:s')
        ];
        MessageReceiver::insert($data);
    }

    public function sendQuoteMail($projectData, $email) {
        if (!empty($email)) {
            app(Dispatcher::class)->dispatch
                    (new SendMailJob([
                'projectData' => $projectData,
                'email' => $email,
                'supplierName' => $this->supplierName,
                    ], 'PROJECT_QUOTE'));
        }
    }

    /**
     * @param int $quoteId
     * @param Request $request
     * 
     * @return array
     */
    public function quoteEdited($quoteId, Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $supplier = BaseSupplier::where('id', $supplierId)
                ->selectRaw('id,status,deleted_flag,enable,source')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        check($supplier->status === 'APPROVED', '供应商没有准入');
        check($supplier->enable == '1', '供应商已被禁用');
        $base = $request->base;
        $quote = ProjectBidQuote::where('project_id', $base['project_id'])
                ->selectRaw('bid_status,supplier_id,id')
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->first();
        if (empty($quote)) {
            check(false, '投标不存在');
        }
        if ($quote->bid_status != 'A') {
            check(false, '已提交无法修改');
        }
        $projectSupplier = ProjectSupplier::where('project_id', $base['project_id'])
                ->where('supplier_id', $supplierId)
                ->first();
        if (!empty($projectSupplier) && $projectSupplier->shortlist_flag != 'Y') {
            check(false, '供应商未入围');
        }
        $project = Project::where('id', $base['project_id'])->first();
        $projectSubObj = ProjectSub::where('project_id', $base['project_id'])
                ->selectRaw('tender_fee,deposit,is_deposit,deposit_stage')
                ->first();
        check(!empty($projectSubObj), '招标不存在');
        $sub = $projectSubObj->toArray();
        if ($sub['tender_fee'] == '0' && ($sub['deposit_stage'] == 2 || ($sub['deposit_stage'] == 1 && $deposit == 0))) {
            
        } elseif (!empty($projectSupplier) && $projectSupplier->pay_flag != 'Y' && $base['bid_status'] === 'C') {
            check(false, '供应商未完成缴费确认');
        }
        $currentStepList = explode(',', $project->current_step);
        if (!in_array('F', $currentStepList)) {
            check(false, '当前不是发标状态');
        }
        $quoteData = [
            'project_id' => $base['project_id'],
            'supplier_id' => $supplierId,
            'inclu_tax_amount' => !empty($base['inclu_tax_amount']) ? $base['inclu_tax_amount'] : '',
            'tax_amount' => !empty($base['tax_amount']) ? $base['tax_amount'] : '',
            'except_tax_amount' => !empty($base['except_tax_amount']) ? $base['except_tax_amount'] : '',
            'project_manager' => !empty($base['project_manager']) ? $base['project_manager'] : '',
            'work_load' => !empty($base['work_load']) ? $base['work_load'] : '',
            'bid_status' => !empty($base['bid_status']) ? $base['bid_status'] : '',
            'comment' => !empty($base['comment']) ? $base['comment'] : '',
            'tended_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
        ];
        $flag = ProjectBidQuote::where('id', $quoteId)->update($quoteData);
        (new ProjectBidAttachRepo)->updateData($quoteId, $request);
        (new ProjectBidEntryRepo())->updateData($quoteId, $request);
        if ($quoteData['bid_status'] === 'C') {
            ProjectSupplier::where('project_id', $base['project_id'])
                    ->where('supplier_id', $supplierId)
                    ->update([
                        'status' => 'I',
                        'tended_by' => $admin->user_id,
                        'is_tender' => '1',
                        'tended_at' => date('Y-m-d H:i:s')
            ]);
            $this->sendQuote($base['project_id']);
        }
        return $flag;
    }

    public function quoteinfo($projectId) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $supplier = BaseSupplier::where('id', $supplierId)
                ->selectRaw('id,status,deleted_flag,enable')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        check($supplier->status === 'APPROVED', '供应商没有准入');
        check($supplier->enable == '1', '供应商已被禁用');
        $base = ProjectBidQuote::selectRaw('*')->where('project_id', $projectId)->where('supplier_id', $supplierId)->first();
        if (empty($base)) {
            return [];
        }
        $data['base'] = $base;
        $data['attachs'] = (new ProjectBidAttachRepo)->getList($base['id']);
        $data['entrys'] = (new ProjectBidEntryRepo)->getList($base['id']);
        return $data;
    }

    public function getProcess($projectId) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $projectObj = Project::where('id', $projectId)->first();
        if (empty($projectObj)) {
            check(false, '招标不存在');
        }
        $project = $projectObj->toArray();
        $setupDate = $project['setup_date'];
        $projectSubObj = ProjectSub::where('project_id', $projectId)->first();
        $projectSupplier = ProjectSupplier::where('project_id', $projectId)->where('supplier_id', $supplierId)->first();
        if (empty($projectSupplier)) {
            check(false, '您没有参与改招标项目');
        }
        $currentStep = $project['current_step'];
        $sub = !empty($projectSubObj) ? $projectSubObj->toArray() : [];
        $invitationEndDate = !empty($sub) ? $sub['invitation_deadline'] : null;
        $currentSteps = explode(',', $currentStep);
        $currentStepArr = [];
        foreach ($currentSteps as $current_step) {
            if (!empty($current_step)) {
                $currentStepArr[] = ord($current_step);
            }
        }

        $active = 'Unstart';
        if (in_array('A', $currentSteps)) {
            $active = 'Ongoing';
        } else {
            $active = 'Done';
        }
        $invitationActive = 'Unstart';
        if (in_array('B', $currentSteps)) {
            $invitationActive = 'Ongoing';
        } elseif ($project['supplier_invitation'] == '1') {
            $invitationActive = 'Done';
        }

        $payActive = 'Unstart';
        if ($sub['charging_stage'] == '1' && (in_array('B', $currentSteps) || in_array('C', $currentSteps) || in_array('F', $currentSteps)) && $projectSupplier['pay_flag'] != 'Y' && $project['bid_open_deadline'] > date('Y-m-d H:i:s')) {
            $payActive = 'Ongoing';
        } elseif ($sub['charging_stage'] == '1' && $projectSupplier['pay_flag'] === 'Y' || $project['bid_open_deadline'] < date('Y-m-d H:i:s')) {
            $payActive = 'Done';
        } elseif ($sub['charging_stage'] == '2' && empty($sub['tender_fee'])) {
            $payActive = 'Hidden';
        }

        $documentAt = null;
        $documentActive = 'Unstart';
        if (in_array('C', $currentSteps)) {
            $documentActive = 'Ongoing';
        } elseif ($project['bid_document'] == '1') {
            $documentObj = ProjectDocument::where('project_id', $projectId)->selectRaw('document_at,bill_status')->first();
            $docDownloadFlag = ProjectSupplier::where('project_id', $projectId)->where('supplier_id', $supplierId)
                    ->value('doc_download_flag');
            $documentAt = !empty($documentObj) ? $documentObj->document_at : null;
            $documentActive = $docDownloadFlag == 'Y' ? 'Done' : 'Ongoing';
        }
        $publishObj = ProjectPublish::where('project_id', $projectId)->selectRaw('publish_at,publish_status')->first();
        $publishAt = null;
        $publishActive = 'Unstart';
        $isTender = ProjectSupplier::where('project_id', $projectId)->where('supplier_id', $supplierId)
                ->value('is_tender');
        if (in_array('F', $currentSteps) && $publishObj['publish_status'] == 'C') {

            if ($isTender == '1') {
                $publishActive = 'Done';
            } else {
                $publishActive = 'Ongoing';
            }
        } elseif ($project['bid_publish'] == '1') {
            if ($isTender == '1') {
                $publishActive = 'Done';
            } elseif (in_array($project['current_step'], ['H', 'I', 'K'])) {
                $publishActive = 'Not_participating';
            }
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

        $decisionObj = ProjectDecision::where('project_id', $projectId)->selectRaw('decision_at,decision_status')->first();
        $decisionAt = null;
        $decisionActive = 'Unstart';
        if ($project['bid_decision'] == '1' && $decisionObj->decision_status === 'C' && $isTender == '1') {
            $decisionAt = !empty($decisionObj) ? $decisionObj->decision_at : null;
            $decisionActive = 'Done';
        } elseif ($project['bid_decision'] == '1' && $decisionObj->decision_status === 'C' && $isTender !== '1') {
            $decisionActive = 'Not_participating';
        }

        return [
            'signup' => [
                'real_at' => $setupDate,
                'active' => $active,
                'estimate_at' => $invitationEndDate,
            ],
            'invitation' => [
                'real_at' => $project['shortlist_at'],
                'active' => $invitationActive,
                'estimate_at' => $project['supplier_invi_end_date'],
            ],
            'pay' => [
                'real_at' => $project['supplier_invi_end_date'],
                'payActive' => $payActive,
                'estimate_at' => $project['technical_doc_end_date'],
            ],
            'document' => [
                'real_at' => $documentAt,
                'active' => $documentActive,
                'estimate_at' => $project['technical_doc_end_date'],
            ],
            'quote' => [
                'real_at' => $publishAt,
                'active' => $publishActive,
                'estimate_at' => $project['bid_publish_date'],
            ],
            'decision' => [
                'real_at' => $decisionAt,
                'active' => $decisionActive,
                'estimate_at' => $project['bid_decision_date'],
            ],
        ];
    }

    public function download($projectId, string $group, Request $request) {
        if (empty($projectId)) {
            return [];
        }
        $docId = $request->doc_id;
        $qurey = ProjectAttach::selectRaw('*');
        $qurey->where('project_id', $projectId);
        $qurey->where('group', $group);
        if (!empty($docId)) {
            $qurey->where('id', $docId);
        }
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        if (empty($list)) {
            return [];
        }
        switch ($group) {
            case 'COMMERCIAL':
                $filename = '商务标文件_' . $projectId . '.zip';
                break;
            case 'TECHNICAL':
                $filename = '技术标文件_' . $projectId . '.zip';
                break;
        }
        if (empty($docId)) {
            $filepath = $this->packAndUpload($filename, $list);
        } else {
            $filepath = $list[0]['attach_url'];
            $filename = $list[0]['attach_name'];
        }
        ProjectSupplierDownload::insert([
            'group' => $group,
            'project_id' => $projectId,
            'doc_id' => !empty($docId) ? $docId : null,
            'supplier_id' => $this->supplierId,
            'created_by' => $this->userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        if (empty($docId)) {
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            unlink($filepath);
        } else {
            $con = file_get_contents($filepath);
            header('Content-Length: ' . strlen($con));
            echo $con;
        }
        exit;
    }

    public function setDownloadFlag($projectId) {
        $docDownloadFlag = ProjectSupplier::where('project_id', $projectId)
                ->where('supplier_id', $this->supplierId)
                ->value('doc_download_flag');
        if ($docDownloadFlag === 'Y') {
            return;
        }
        $supplierId = $this->supplierId;
        $attach = (new ProjectAttach)->getTable();
        $downloadObj = ProjectAttach::from($attach . ' as a')
                ->where('a.project_id', $projectId)
                ->whereIn('a.group', ['TECHNICAL', 'COMMERCIAL'])
                ->selectRaw('a.id,a.group')
                ->get();
        if (empty($downloadObj)) {

            return;
        }
        $downloadList = $downloadObj->toArray();
        $groupObj = ProjectSupplierDownload::where('project_id', $projectId)
                ->where('supplier_id', $supplierId)
                ->whereNull('doc_id')
                ->pluck('group');
        $docIdObj = ProjectSupplierDownload::where('project_id', $projectId)
                ->where('supplier_id', $supplierId)
                ->where('doc_id', '>', 0)
                ->pluck('doc_id');
        $groupList = !empty($groupObj) ? array_unique($groupObj->toArray()) : [];
        $docIdList = !empty($docIdObj) ? array_unique($docIdObj->toArray()) : [];
        $groupArr = array_unique(array_column($downloadList, 'group'));
        $docIdArr = [];
        foreach ($downloadList as $download) {
            $docIdArr[$download['group']][] = $download['id'];
        }
        $notGroup = [];
        foreach ($groupArr as $group) {
            if (!in_array($group, $groupList)) {
                $notGroup[] = $group;
            }
        }
        if (empty($notGroup)) {
            ProjectSupplier::where('project_id', $projectId)
                    ->where('supplier_id', $this->supplierId)
                    ->update(['doc_download_flag' => 'Y']);
        }
        foreach ($notGroup as $group) {
            $docIds = $docIdArr[$group];
            foreach ($docIds as $docId) {
                if (!in_array($docId, $docIdList)) {
                    return;
                }
            }
        }
        ProjectSupplier::where('project_id', $projectId)
                ->where('supplier_id', $this->supplierId)
                ->update(['doc_download_flag' => 'Y']);
    }

    private function RecursiveMkdir($path) {
        if (!file_exists($path)) {
            $this->RecursiveMkdir(dirname($path));
            @mkdir($path, 0777);
        }
    }

    /**
     * 打包文件并且上传至FastDFS服务器
     * @param string $filename 压缩包名称
     * @return mixed
     * */
    private function packAndUpload($filename, $list) {
        //创建临时目录
        $ds = DIRECTORY_SEPARATOR;
        $relativeDir = $ds . 'download' . $ds . date('Ymd') . $ds . uniqid() . $ds;
        $tmpDir = base_path() . $ds . 'public' . $relativeDir;
        $this->RecursiveMkdir($tmpDir);
        //复制文件到临时目录
        foreach ($list as $file) {
            $name = $file['attach_name'];
            //如果文件存在则重命名
            if (file_exists($tmpDir . $name)) {
                //循环100次修改文件名
                for ($i = 1; $i < 100; $i++) {
                    $name = preg_replace("/(\.\w+)/i", "($i)$1", $name);
                    if (!file_exists($tmpDir . $name)) {
                        break;
                    }
                }
            }
            //目标文件仍然存在，则写入错误文件
            if (file_exists($tmpDir . $name)) {
                $error_files[] = $file;
            }
            $fileName = iconv('utf-8', 'gbk', $name);
            $content = file_get_contents($file['attach_url']);
            file_put_contents($tmpDir . $fileName, $content);
        }
        //如果有文件无法复制到本目录
        if (!empty($error_files)) {
            return false;
        }
        //生成压缩文件
        $zip = new \ZipArchive();
        $filepath = dirname($tmpDir) . '/' . $filename;
        $res = $zip->open($filepath, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE);
        if ($res !== true) {
            return false;
        }
        $file_arr = scandir($tmpDir);
        foreach ($file_arr as $item) {
            if ($item != '.' && $item != '..') {
                $zip->addFile($tmpDir . $item, $item);
            }
        }

        $zip->close();
        //清理临时目录
        foreach ($file_arr as $item) {
            if ($item != '.' && $item != '..') {
                unlink($tmpDir . $item);
            }
        }
        rmdir($tmpDir);
        return $filepath;
    }

}
