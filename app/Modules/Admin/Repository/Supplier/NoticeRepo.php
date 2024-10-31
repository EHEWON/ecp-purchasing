<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    BidBill\BidBillSupplier,
    Inquiry\Supplier,
    Notice,
    NoticeSub,
    NoticeSupplier,
    Project\ProjectSupplier,
    Purchaser,
    UserSupplier
};
use App\Modules\Admin\Repository\{
    Inquiry\InquiryRepo,
    Inquiry\SupplierRepo,
    NoticeAttachRepo,
    NoticeSupplierRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticeRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'title',
        'org_id',
        'bill_date',
        'is_top',
        'due_date',
        'src_bill_no',
        'sup_scope',
        'biz_type',
    ];

    public function __construct() {
        $this->model = new Notice();
        parent::__construct($this->model);
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $query->orderBy('n.is_top', 'DESC');
        $query->orderBy('nss.read_flag', 'ASC');
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'bill_date';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'bill_date') {
            $query->orderBy('bill_date', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            return [];
        }
        $noticeTable = $this->model->getTable();
        $supplierTable = (new Supplier)->getTable();
        $bidBillSupplierTable = (new BidBillSupplier())->getTable();
        $projectSupplierTable = (new ProjectSupplier())->getTable();
        $noticeSupplierTable = (new NoticeSupplier())->getTable();
        $subTable = (new NoticeSub)->getTable();
        $query = $this->model
                ->selectRaw('n.id,n.bill_no,n.bill_date,n.due_date,'
                        . 'n.biz_type,n.sup_scope,n.bill_status,n.org_id,n.title,'
                        . 'ns.src_bill_id,ns.src_bill_no,ns.src_bill_type,n.is_top')
                ->from($noticeTable . ' as n')
                ->join($subTable . ' AS ns', function ($join) {
                    $join->on('n.id', '=', 'ns.notice_id');
                })
                ->leftJoin($noticeSupplierTable . ' AS nss', function ($join)use($supplierId) {
                    $join->on('n.id', '=', 'nss.notice_id')
                    ->where('nss.supplier_id', '=', $supplierId);
                })
                ->whereIn('ns.src_bill_type', ['sou_inquiry', 'sou_compare', 'sou_bidbill', 'sou_bidbillcfm', 'sou_project', 'sou_decision'])
                ->whereIn('n.biz_type', ['1', 'A', '3', 'B', '2', '5'])
                ->whereRaw('(sup_scope=\'1\' OR (sup_scope=\'2\' AND ('
                . 'EXISTS(SELECT bbs.id FROM ' . $bidBillSupplierTable . ' as bbs where bbs.supplier_id='
                . $supplierId . ' AND bbs.bid_bill_id=ns.src_bill_id AND'
                . '(( bbs.entry_status NOT IN(\'WCY\',\'N\')AND n.biz_type=\'3\') OR (n.biz_type=\'A\' AND '
                . 'bbs.entry_status=\'F\')) AND bbs.deleted_flag=\'N\')'
                . 'OR EXISTS(SELECT iss.id FROM ' . $supplierTable . ' as iss where iss.supplier_id='
                . $supplierId . ' AND iss.inquiry_id=ns.src_bill_id AND '
                . '(( iss.supplier_biz_status NOT IN(\'C\',\'D\') AND n.biz_type=\'1\') OR(n.biz_type=\'B\' AND iss.entry_status IN (\'C\',\'D\'))   )'
                . 'AND iss.deleted_flag=\'N\')'
                . 'OR EXISTS(SELECT pss.id FROM ' . $projectSupplierTable . ' as pss where pss.supplier_id='
                . $supplierId . ' AND pss.project_id=ns.src_bill_id AND ((pss.status NOT IN(\'N\',\'WCY\')   AND n.biz_type=\'2\') '
                . 'OR ( n.biz_type=\'5\' AND pss.status=\'F\')) '
                . 'AND pss.deleted_flag=\'N\')'
                . ')))');
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
        $inquiryRepo = new InquiryRepo();
        $supplierRepo = new SupplierRepo();
        $psupplierRepo = new \App\Modules\Admin\Repository\Project\ProjectSupplierRepo();
        $this->setInquirySuppliers($data, $supplierId);
        $this->setBidBillSuppliers($data, $supplierId);
        $this->setProjectSuppliers($data, $supplierId);
        foreach ($data as &$item) {
            $item['supplier_id'] = (string) $supplierId;
            $item['is_id'] = !empty($item['is_id']) ? $item['is_id'] : '';
            $item['src_bill_id'] = (string) $item['src_bill_id'];
            $item['biz_type_name'] = $this->getBizTypeText($item['biz_type']);
            $item['bill_status_name'] = $this->getStatusText($item['bill_status']);
            $item['src_bill_type_name'] = $this->getSrcBillTypeText($item['src_bill_type']);
            $item['supplier_biz_status'] = !empty($item['supplier_biz_status']) ? $item['supplier_biz_status'] : '';
            $item['entry_status'] = !empty($item['entry_status']) ? $item['entry_status'] : '';
            $item['supplier_biz_status_name'] = $supplierRepo->getBizStatusText($item['supplier_biz_status']);
            if (in_array($item['biz_type'], ['2', '5'])) {
                $item['entry_status_name'] = $psupplierRepo->getBillStatusText($item['entry_status']);
            } else {
                $item['entry_status_name'] = $supplierRepo->getEntryStatusText($item['entry_status']);
            }
            $item['sup_scope_name'] = $inquiryRepo->getSupScopeText($item['sup_scope']);
        }
        (new NoticeSupplierRepo)->setReadFlags($data, $supplierId);
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            return [];
        }
        $noticeTable = $this->model->getTable();
        $subTable = (new NoticeSub)->getTable();
        $noticeSupplier = (new NoticeSupplier)->getTable();
        $query = $this->model
                ->selectRaw('n.*,ns.*,nsu.supplier_id,nsu.read_flag')
                ->from($noticeTable . ' as n')
                ->join($subTable . ' as ns', function ($join) {
                    $join->on('n.id', '=', 'ns.notice_id');
                })
                ->leftJoin($noticeSupplier . ' as nsu', function($join)use($supplierId) {
                    $join->on('nsu.notice_id', '=', 'n.id')
                    ->where('nsu.supplier_id', $supplierId);
                })
                ->where('n.id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $inquiryRepo = new InquiryRepo();
        $supplierRepo = new SupplierRepo();
        $this->setInquirySupplier($data, $supplierId);
        $this->setBidBillSupplier($data, $supplierId);
        $this->seProjectSupplier($data, $supplierId);
        (new \App\Modules\Admin\Repository\SupplierContactRepo())->setDefaultContact($data);
        (new NoticeSupplierRepo)->setReadFlag($data, $supplierId);
        $data['src_bill_id'] = (string) $data['src_bill_id'];
        $data['notice_id'] = (string) $data['notice_id'];
        $data['modifier_id'] = (string) $data['modifier_id'];
        $data['auditor_id'] = (string) $data['auditor_id'];
        $data['creator_id'] = (string) $data['creator_id'];
        (new UserRepo)->setUser($data, 'modifier_id', 'modifier_name');
        (new UserRepo)->setUser($data, 'auditor_id', 'auditor_name');
        (new UserRepo)->setUser($data, 'creator_id', 'creator_name');
        $data['biz_type_name'] = $this->getBizTypeText($data['biz_type']);
        $data['bill_status_name'] = $this->getStatusText($data['bill_status']);
        $data['sup_scope_name'] = (new InquiryRepo)->getSupScopeText($data['src_bill_type']);
        $data['src_bill_type_name'] = $this->getSrcBillTypeText($data['src_bill_type']);
        $data['sup_scope_name'] = $inquiryRepo->getSupScopeText($data['sup_scope']);
        $data['supplier_biz_status_name'] = $supplierRepo->getBizStatusText($data['supplier_biz_status']);
        $data['entry_status_name'] = $supplierRepo->getEntryStatusText($data['entry_status']);
        $data['attach'] = (new NoticeAttachRepo)->getList($id);
        if (empty($data['supplier_id'])) {
            NoticeSupplier::insert([
                'notice_id' => $id,
                'supplier_id' => $supplierId,
                'read_flag' => 'Y',
                'created_at' => date('Y-m-d H:i:s'),
                'contacter' => !empty($data['contact_name']) ? $data['contact_name'] : '',
                'phone' => !empty($data['contact_phone']) ? $data['contact_phone'] : '',
                'email' => !empty($data['contact_email']) ? $data['contact_email'] : '',
            ]);
        }
        NoticeSupplier::where('notice_id', $id)
                ->where('supplier_id', $supplierId)
                ->update(['read_flag' => 'Y']);
        return $data;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function projectInfo($projectId, $bizType = '2', $supScope = ['1']) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            return [];
        }
        $noticeTable = $this->model->getTable();
        $subTable = (new NoticeSub)->getTable();
        $query = $this->model
                ->selectRaw('n.bill_date,n.id,n.org_id,n.content,ns.src_bill_id,ns.notice_id,n.title')
                ->from($noticeTable . ' as n')
                ->leftJoin($subTable . ' as ns', function ($join) {
                    $join->on('n.id', '=', 'ns.notice_id');
                })
                ->where('n.biz_type', $bizType)
                ->whereIn('n.sup_scope', $supScope)
                ->where('ns.src_bill_id', $projectId);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $data['src_bill_id'] = (string) $data['src_bill_id'];
        $data['name'] = (string) $data['title'];
        $data['created_at'] = (string) $data['bill_date'];
        $data['notice_id'] = (string) $data['notice_id'];
        return $data;
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {

        $query->where('n.bill_status', 'C');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('n.title', 'like', '%' . $keyword . '%')
                        ->orWhere('n.bill_no', 'like', '%' . $keyword . '%')
                        ->orWhere('ns.src_bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->read_flag)) {
            $readFlag = trim($request->read_flag);
            $readFlag == 'N' ? $query->whereRaw('ISNULL(nss.read_flag)') : $query->whereRaw('nss.id IS NOT NULL')
                   ->whereRaw('nss.read_flag=\'Y\'');
        }

        if (!empty($request->biz_type) && !empty($request->notice_type)) {
            switch (strtoupper($request->biz_type)) {
                case 'INQUIRY':
                    switch (strtoupper($request->notice_type)) {
                        case 'PURCHASE':
                            $query->where('p.biz_type', '1');
                            break;
                        case 'RESULT':
                            $query->where('p.biz_type', 'A');
                            break;
                    }
                    break;
                case 'BIDDING':
                    switch (strtoupper($request->notice_type)) {
                        case 'PURCHASE':
                            $query->where('p.biz_type', '3');
                            break;
                        case 'RESULT':
                            $query->where('p.biz_type', 'B');
                            break;
                    }
                    break;
            }
        } elseif (!empty($request->biz_type)) {
            switch (strtoupper($request->biz_type)) {
                case 'INQUIRY':
                    $query->whereIn('p.biz_type', ['1', 'A']);
                case 'BIDDING':
                    $query->whereIn('p.biz_type', ['3', 'B']);
            }
        } elseif (!empty($request->notice_type)) {
            switch (strtoupper($request->notice_type)) {
                case 'PURCHASE':
                    $query->whereIn('p.biz_type', ['1', '3']);
                case 'RESULT':
                    $query->whereIn('p.biz_type', ['A', 'B']);
            }
        }
        if (!empty($request->notice_status)) {
            switch (strtoupper($request->notice_type)) {
                case 'PROGRESSING':
                    $query->where('p.due_date', '>', date('Y-m-d H:i:s'));
                case 'EXPIRED':
                    $query->where('p.due_date', '<=', date('Y-m-d H:i:s'));
            }
        }

        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('n.bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('n.bill_date', $createAts);
        }
    }

    public function getStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '保存';
            case 'B':
                return '已提交';
            case 'C':
                return '已审核';
        }
    }

    public function getStatusList() {
        return [
            'A' => '保存',
            'B' => '已提交',
            'C' => '已审核',
        ];
    }

    /**
     * 
     * 1询价公告 2招标公告 3 竞价公告 4比价公告 5 中标公告  6招募公告 7 行业动态 8系统公告  A 询价结果公告  B竞价结果公告
     */
    public function getBizTypeText($bizType) {
        switch (strtoupper($bizType)) {
            case '1':
                return '询价公告';
            case '2':
                return '招标公告';
            case '3':
                return '竞价公告';
            case '4':
                return '比价公告';
            case '5':
                return '中标公告';
            case '6':
                return '招募公告';
            case '7':
                return '行业动态';
            case '8':
                return '系统公告';
            case 'A':
                return '询价结果公告';
            case 'B':
                return '竞价结果公告';
        }
    }

    /**
     * 
     * 1询价公告 2招标公告 3 竞价公告 4比价公告 5 中标公告  6招募公告 7 行业动态 8系统公告  A 询价结果公告  B竞价结果公告
     */
    public function getSrcBillTypeText($srcBillType) {
        switch (strtolower($srcBillType)) {
            case 'sou_inquiry':
                return '询价';
            case 'sou_compare':
                return '比价';
        }
    }

    public function getBizTypeList() {
        return ['1' => '询价公告',
            '2' => '招标公告',
            '3' => '竞价公告',
            '4' => '比价公告',
            '5' => '中标公告',
            '6' => '招募公告',
            '7' => '行业动态',
            '8' => '系统公告',
            'A' => '询价结果公告',
            'B' => '竞价结果公告',
        ];
    }

    public function getSrcBillTypeList() {
        return ['sou_inquiry' => '询价',
            'sou_compare' => '比价'
        ];
    }

    public function setInquirySuppliers(&$data, $supplierId) {
        if (empty($data)) {
            return;
        }
        $inquiryIds = [];
        foreach ($data as &$item) {
            if (!in_array($item['biz_type'], ['1', 'A'])) {
                continue;
            }
            $item['is_id'] = '';
            $item['supplier_biz_status'] = '';
            $item['entry_status'] = '';
            $inquiryIds[] = $item['src_bill_id'];
        }
        if (empty($inquiryIds)) {
            return;
        }
        $list = Supplier::selectRaw('inquiry_id,GROUP_CONCAT(supplier_biz_status) as ssupplier_biz_status ,'
                        . 'GROUP_CONCAT(entry_status) as sentry_status,max(id) AS sid')
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->whereIn('inquiry_id', $inquiryIds)
                ->groupBy('inquiry_id')
                ->get()
                ->toArray();
        $inquiryArr = [];
        foreach ($list as $val) {
            $inquiryArr[$val['inquiry_id']] = $val;
        }
        foreach ($data as &$item) {
            if (!in_array($item['biz_type'], ['3', 'B'])) {
                continue;
            }
            if (empty($inquiryArr[$item['src_bill_id']])) {
                continue;
            }
            $inquiry = $inquiryArr[$item['src_bill_id']];
            $item['is_id'] = $inquiry['sid'];
            $item['supplier_biz_status'] = $inquiry['ssupplier_biz_status'];
            $item['entry_status'] = $inquiry['sentry_status'];
        }
    }

    public function setProjectSuppliers(&$data, $supplierId) {
        if (empty($data)) {
            return;
        }
        $projectIds = [];
        foreach ($data as &$item) {
            if (!in_array($item['biz_type'], ['2', '5'])) {
                continue;
            }
            $item['is_id'] = '';
            $item['supplier_biz_status'] = '';
            $item['entry_status'] = '';
            $projectIds[] = $item['src_bill_id'];
        }
        if (empty($projectIds)) {
            return;
        }
        $list = ProjectSupplier::selectRaw('project_id,'
                        . 'GROUP_CONCAT(status) as sentry_status,max(id) AS sid')
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->whereIn('project_id', $projectIds)
                ->groupBy('project_id')
                ->get()
                ->toArray();
        $projectArr = [];
        foreach ($list as $val) {
            $projectArr[$val['project_id']] = $val;
        }
        foreach ($data as &$item) {
            if (!in_array($item['biz_type'], ['2', '5'])) {
                continue;
            }
            if (empty($projectArr[$item['src_bill_id']])) {
                continue;
            }
            $project = $projectArr[$item['src_bill_id']];
            $item['is_id'] = $project['sid'];
            $item['supplier_biz_status'] = '';
            $item['entry_status'] = $project['sentry_status'];
        }
    }

    public function setBidBillSuppliers(&$data, $supplierId) {
        if (empty($data)) {
            return;
        }
        $bidBillIds = [];
        foreach ($data as &$item) {
            if (!in_array($item['biz_type'], ['3', 'B'])) {
                continue;
            }
            $item['is_id'] = '';
            $item['supplier_biz_status'] = '';
            $item['entry_status'] = '';
            $bidBillIds[] = $item['src_bill_id'];
        }
        if (empty($bidBillIds)) {
            return;
        }
        $list = BidBillSupplier::selectRaw('bid_bill_id,'
                        . 'GROUP_CONCAT(entry_status) as sentry_status,max(id) AS sid')
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->whereIn('bid_bill_id', $bidBillIds)
                ->groupBy('bid_bill_id')
                ->get()
                ->toArray();
        $bidBillArr = [];
        foreach ($list as $val) {
            $bidBillArr[$val['bid_bill_id']] = $val;
        }
        foreach ($data as &$item) {
            if (!in_array($item['biz_type'], ['3', 'B'])) {
                continue;
            }
            if (empty($bidBillArr[$item['src_bill_id']])) {
                continue;
            }
            $bidBill = $bidBillArr[$item['src_bill_id']];
            $item['is_id'] = $bidBill['sid'];
            $item['supplier_biz_status'] = '';
            $item['entry_status'] = $bidBill['sentry_status'];
        }
    }

    public function setInquirySupplier(&$item, $supplierId) {
        if (empty($item)) {
            return;
        }

        if (!in_array($item['biz_type'], ['1', 'A'])) {
            return;
        }
        $item['is_id'] = '';
        $item['supplier_biz_status'] = '';
        $item['entry_status'] = '';
        $inquiryId = $item['src_bill_id'];
        if (empty($inquiryId)) {
            return;
        }
        $supplier = Supplier::selectRaw('inquiry_id,GROUP_CONCAT(supplier_biz_status) as ssupplier_biz_status ,'
                        . 'GROUP_CONCAT(entry_status) as sentry_status,max(id) AS sid')
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->where('inquiry_id', $inquiryId)
                ->groupBy('inquiry_id')
                ->first();
        if (empty($supplier)) {
            return;
        }
        if (empty($supplier->toArray())) {
            return;
        }
        $item['is_id'] = $supplier->sid;
        $item['supplier_biz_status'] = $supplier->ssupplier_biz_status;
        $item['entry_status'] = '';
    }

    public function setBidBillSupplier(&$item, $supplierId) {
        if (empty($item)) {
            return;
        }

        $bidBillId = $item['src_bill_id'];
        if (!in_array($item['biz_type'], ['3', 'B'])) {
            return;
        }
        $item['is_id'] = '';
        $item['supplier_biz_status'] = '';
        $item['entry_status'] = '';

        $supplier = BidBillSupplier::selectRaw('bid_bill_id,'
                        . 'GROUP_CONCAT(entry_status) as sentry_status,max(id) AS sid')
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->where('bid_bill_id', $bidBillId)
                ->groupBy('bid_bill_id')
                ->first();
        if (empty($supplier)) {
            return;
        }
        if (empty($supplier->toArray())) {
            return;
        }
        $item['is_id'] = $supplier->sid;
        $item['supplier_biz_status'] = '';
        $item['entry_status'] = $supplier->sentry_status;
    }

    public function seProjectSupplier(&$item, $supplierId) {
        if (empty($item)) {
            return;
        }

        $projectId = $item['src_bill_id'];
        if (!in_array($item['biz_type'], ['2', '5'])) {
            return;
        }
        $item['is_id'] = '';
        $item['supplier_biz_status'] = '';
        $item['entry_status'] = '';

        $supplier = ProjectSupplier::selectRaw('project_id,'
                        . 'GROUP_CONCAT(status) as sentry_status,max(id) AS sid')
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->where('project_id', $projectId)
                ->groupBy('project_id')
                ->first();
        if (empty($supplier)) {
            return;
        }
        if (empty($supplier->toArray())) {
            return;
        }
        $item['is_id'] = $supplier->sid;
        $item['supplier_biz_status'] = '';
        $item['entry_status'] = $supplier->sentry_status;
    }

}
