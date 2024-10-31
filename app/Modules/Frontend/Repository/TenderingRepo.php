<?php

namespace App\Modules\Frontend\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Notice,
    Project\Project,
    Project\ProjectSub,
    Project\ProjectSupplier,
    NoticeSub
};
use \App\Modules\Admin\Repository\{
    UserRepo,
    SupplierBaseRepo,
    OrgRepo
};
use Illuminate\Http\Request;

class TenderingRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Project();
        parent::__construct($this->model);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request) {
        $notice = (new Notice())->getTable();
        $noticeSub = (new NoticeSub())->getTable();
        $qurey = Notice::from($notice . ' AS i')
                ->leftJoin($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->selectRaw('i.id,i.title,i.bill_date as publish_date'
                        . ',\'inquiry\' AS type,i.org_id,i.biz_type AS biztype,due_date AS duedate,'
                        . 'i.bill_status AS `status`')
                ->whereRaw('i.bill_status=\'C\'')
                ->whereRaw('i.sup_scope=\'1\'')
                ->whereRaw('i.biz_type IN (\'2\',\'5\')');
        $this->getWhere($qurey, $request->all());
        $clone = $qurey->clone();
        $qurey->orderBy('publish_date', 'desc');
        $this->getPage($qurey, $request);
        $object = $qurey->get();
        $total = $clone->count();
        $list = [];
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['id'] = (string) $item['id'];
            if ($item['biztype'] == '2') {
                $item['type'] = 'bid_project';
                $item['type_name'] = '招标';
            } else if ($item['biztype'] == '5') {
                $item['type'] = 'bid_project';
                $item['type_name'] = '定标';
            }
            $item['status_name'] = $this->getStatusName($item['status']);
            $item['publish_date'] = date('Y-m-d', strtotime($item['publish_date']));
            $item['left_time'] = leftTimeDisplay($item['duedate']);
        }
        (new OrgRepo)->setOrgs($data, 'org_id', 'org_name');
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function noticeinfo($id) {
        $notice = (new Notice())->getTable();
        $noticeSub = (new NoticeSub())->getTable();
        $obj = Notice::from($notice . ' AS i')
                ->leftJoin($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->selectRaw('i.id,i.bill_no,i.title,i.bill_date,i.org_id'
                        . ',i.biz_type,i.due_date,ns.src_bill_no,i.bill_status,i.content,'
                        . 'ns.src_bill_id')
                ->where('i.id', $id)
                ->where('i.bill_status', 'C')
                ->first();
        if (empty($obj)) {
            return [];
        }
        $detail = $obj->toArray();
        if (empty($detail)) {
            return [];
        }
        (new OrgRepo)->setOrg($detail, 'org_id', 'org_name');
        $ret = [
            'due_date' => $detail['due_date'],
            'notice_id' => $detail['id'],
            'title' => $detail['title'],
            'org_id' => $detail['org_id'],
            'org_name' => $detail['org_name'],
            'publish_date' => $detail['bill_date'],
            'billno' => $detail['bill_no'],
            'srcbillno' => $detail['src_bill_no'],
            'src_bill_id' => (string) $detail['src_bill_id'],
            'biztype' => $detail['biz_type'],
            'status' => null,
            'content' => $detail['content'],
        ];

        if (empty($detail['biz_type'])) {
            return $ret;
        }
        switch ($detail['biz_type']) {
            case '2':
                $ret['entry'] = (new ProjectEntryRepo)->getList($detail['src_bill_id']);
                $ret['project'] = $this->info($detail['src_bill_id']);
                $ret['left_time'] = leftTimeDisplay($ret['project']['enroll_deadline'] ?? 0);
                $ret['status'] = !empty($ret['project']['current_step']) ? $ret['project']['current_step'] : null;
                break;
            case '5':
                $ret['entry'] = (new ProjectEntryRepo)->getList($detail['src_bill_id']);
                $ret['project'] = $this->info($detail['src_bill_id']);
                $ret['left_time'] = leftTimeDisplay($ret['project']['enroll_deadline'] ?? 0);
                $ret['status'] = !empty($ret['project']['current_step']) ? $ret['project']['current_step'] : null;
                break;
        }

        return $ret;
    }

    protected function getWhere(&$query, array $request) {
        if (!empty($request['keyword'])) {
            $keyword = urldecode(trim($request['keyword']));
            $query->where('title', 'like', '%' . $keyword . '%');
        }

        if (!empty($request['orgs'])) {
            $query->whereIn('org_id', explode(',', urldecode(trim($request['orgs']))));
        }
        if (!empty($request['org_name'])) {
            $query->where('org_name', 'like', '%' . $request['org_name'] . '%');
        }
        if (!empty($request['biztypes'])) {
            $query->whereIn('biztype', explode(',', urldecode(trim($request['biztypes']))));
        }
        if (isset($request['created']) && is_numeric($request['created'])) {
            $createdAt = date('Y-m-d', strtotime('-' . intval($request['created']) . ' days'));
            $query->where('publish_date', '>=', trim($createdAt));
        }
        if (isset($request['published_ats']) && is_array($request['published_ats'])) {
            $query->whereBetween('publish_date', $request['published_ats']);
        }
        if (isset($request['expired'])) {
            if ($request['expired'] == 'Y') {
                $query->where('duedate', '<', date('Y-m-d'));
            } else if ($request['expired'] == 'N') {
                $query->where('duedate', '>=', date('Y-m-d'));
            }
        }
        if (!empty($request['types'])) {
            $query->whereIn('biztype', explode(',', urldecode(trim($request['types']))));
        }
    }

    /**
     * 获取合同列表
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($projectId) {
        $project = (new Project)->getTable();
        $sub = (new ProjectSub)->getTable();
        $baseObj = Project::from($project . ' as p')
                ->join($sub . ' as ps', function($join) {
                    $join->on('ps.project_id', 'p.id');
                })
                ->where('p.id', $projectId)
                ->selectRaw('p.id,p.`name`,p.bill_no,p.current_step,p.setup_date,p.enroll_deadline,p.bid_decision,'
                        . 'p.bid_publish_date,p.bid_open_deadline,p.bid_evaluation_date,p.bid_decision_date,p.supplier_invitation,'
                        . 'ps.tender_fee,ps.deposit,ps.bid_open_type,p.qualification_required,p.pur_description,p.org_id,'
                        . 'p.contact_id,p.contact_tel,p.bid_document,p.bid_evaluation,p.bid_publish')
                ->first();
        if (empty($baseObj)) {
            return [];
        }
        $supplierId = ProjectSupplier::where('deleted_flag', 'N')
                ->where('status', 'F')
                ->where('project_id', $projectId)
                ->value('supplier_id');
        $base = $baseObj->toArray();
        $base['open_type_name'] = $this->getOpenType($base['bid_open_type']);
        $base['deposit'] = number_format($base['deposit'], 2, '.', ',');
        $base['tender_fee'] = number_format($base['tender_fee'], 2, '.', ',');
        $base['supplier_id'] = $supplierId;
        (new SupplierBaseRepo)->setSupplier($base, 'supplier_id', 'supplier_name');
        (new UserRepo)->setUser($base, 'contact_id', 'contact_name');
        return $base;
    }

    public function getTaxCalTypeText($taxCalType) {
        switch (strtoupper($taxCalType)) {
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

    private function getStatusName($status) {
        switch (strtoupper($status)) {
            case 'A': return '采购公告';
            case 'B': return '报价阶段';
            case 'C': return '开标阶段';
            case 'D': return '比价阶段';
            case 'E': return '结果公示';
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

}
