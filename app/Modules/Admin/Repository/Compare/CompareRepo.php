<?php

namespace App\Modules\Admin\Repository\Compare;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Compare\Compare,
    Compare\CompareQuote,
    Compare\Entry,
    Inquiry\Entry as InquiryEntry,
    Inquiry\Inquiry,
    Purchaser,
    Quote\QuoteEntry,
    User
};
use App\Modules\Admin\Repository\{
    Compare\CompareAuditRepo,
    Compare\EntryRepo,
    CurrencyRepo,
    InvoiceTypeRepo,
    NoticeManageRepo,
    PaycondRepo,
    SettleMentTypeRepo,
    SupplierBaseRepo,
    UnitRepo,
    UserRepo,
    OrgRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompareRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'title',
        'biz_status',
        'bill_status',
        'bill_date',
        'end_date',
        'person_id',
    ];

    public function __construct() {
        $this->model = new Compare();
        parent::__construct($this->model);
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'bill_date';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'bill_date') {
            $query->orderBy('compare.bill_date', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = 'compare.id,compare.bill_no,compare.settle_type_id,compare.inquiry_no,compare.inquiry_id,compare.inquiry_title,compare.org_id,compare.bill_date,compare.bill_status,compare.sum_tax_amount,compare.curr_id,compare.audit_status,compare.audit_flag,compare.audit_flag_name,compare.audit_by') {
        $query = $this->model
                ->selectRaw($filed)
                ->join('inquiry', 'inquiry.id', '=', 'compare.inquiry_id', 'left');
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
            $item['sum_tax_amount'] = number_format($item['sum_tax_amount'], 2, '.', ',');
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            (new UserRepo)->setStrUsers($item, 'audit_by', 'audit_names');
            (new CompareQuoteRepo)->setStrSuppliersByCompareId($item, 'id', 'supplier_names');
            $item['related_no'] = \App\Common\Models\Inquiry\Inquiry::where('id', $item['inquiry_id'])->value('related_no');
        }
        (new CurrencyRepo)->setCurrencys($data, 'curr_id', ['cur_name' => 'name', 'cur_number' => 'number', 'cur_sign' => 'sign']);
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
    public function getListGroupBySupplier(Request $request, $filed = 'compare.id,compare_quote.supplier_id,compare.bill_no,compare.settle_type_id,compare.inquiry_no,compare.inquiry_id,compare.inquiry_title,compare.org_id,compare.bill_date,compare.bill_status,compare.sum_tax_amount,compare.curr_id,compare.audit_status,compare.audit_flag,compare.audit_flag_name,compare.audit_by') {
        $query = $this->model
                ->selectRaw($filed)
                ->join('inquiry', 'inquiry.id', '=', 'compare.inquiry_id', 'left')
                ->join('compare_quote', 'compare_quote.compare_id', '=', 'compare.id', 'left');
        $this->getWhere($query, $request);
        $query->where('compare_quote.adopt_flag', '=', 'true');
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
            $item['sum_tax_amount'] = number_format($item['sum_tax_amount'], 2, '.', ',');
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            $item['supplier_id'] = (string) $item['supplier_id'];
            (new UserRepo)->setStrUsers($item, 'audit_by', 'audit_names');
            (new SupplierBaseRepo)->setSuppliers($data, 'supplier_id', 'supplier_name');
            $item['related_no'] = \App\Common\Models\Inquiry\Inquiry::where('id', $item['inquiry_id'])->value('related_no');
        }
        (new OrgRepo)->setOrgs($data, 'org_id', 'org_name');
        (new CurrencyRepo)->setCurrencys($data, 'curr_id', ['cur_name' => 'name', 'cur_number' => 'number', 'cur_sign' => 'sign']);
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array id,bill_no,inquiry_no,inquiry_title,org_id,bill_date,bill_status,sum_tax_amount,curr_id
     */
    public function info($id) {
        $query = $this->model->selectRaw('id,bill_no,inquiry_no,inquiry_id,'
                . 'inquiry_title,org_id,bill_date,end_date,sup_scope,open_type,person_id,'
                . 'phone,related_no,remark,total_inquiry,project_manager,opinion_tag,annual,'
                . 'date_from,audit_status,audit_flag,audit_flag_name,audit_by,bill_status,'
                . 'deli_addr,date_to,settle_type_id,payment_terms,other_pay_terms_info,'
                . 'tax_cal_type,deli_date,curr_id,inv_type,created_by,created_at,updated_by,updated_at');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = [];
        $base = $object->toArray();
        $base['bill_status_name'] = $this->getBillStatusText($base['bill_status']);
        $base['sup_scope_name'] = $this->getSupScopeText($base['sup_scope']);
        $base['open_type_name'] = $this->getOpenTypeText($base['open_type']);
        $base['tax_cal_type_name'] = getTaxCalTypeText($base['tax_cal_type']);
        (new UserRepo)->setUser($base, 'person_id', 'person_name');
        (new UserRepo)->setUser($base, 'project_manager', 'project_manager_name');
        (new UserRepo)->setStrUsers($base, 'audit_by', 'audit_names');
        (new CurrencyRepo)->setCurrency($base, 'curr_id', 'curr_name');
        (new InvoiceTypeRepo)->setInvoiceType($base, 'inv_type', 'inv_type_name');
        (new PaycondRepo)->setPaycond($base, 'payment_terms', ['payment_terms_name' => 'name', 'payment_terms_number' => 'number']);
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settle_type_id', 'settle_type_name');
        (new OrgRepo)->setOrg($base, 'org_id', 'org_name');
        $data['base'] = $base;
        $data['attachs'] = (new AttachRepo)->getList($id);
        $data['entrys'] = (new EntryRepo)->details($id);
        $data['entrysAdopt'] = (new EntryRepo)->details($id, ['adopt_flag' => 'true']);
        $data['quote'] = (new CompareQuoteRepo)->getList($id);
        $data['compareAudit'] = (new CompareAuditRepo)->auditLogs($id);
        return $data;
    }

    /**
     * @param int $compareId
     * @param Request $request
     * 
     * @return array
     */
    public function edited($compareId, Request $request) {
        $base = $request->base;
        $compareObj = Compare::where('id', $compareId)->first();
        $inquiryObj = Inquiry::where('id', $base['inquiry_id'])->first();
        if (empty($inquiryObj)) {
            check(false, '询单不存在');
        }
        if (empty($compareObj)) {
            check(false, '比价单不存在');
        }
        $inquiryInfo = $inquiryObj->toArray();
        if ($inquiryInfo['biz_status'] == 'E') {
            check(false, '询单已终止，无法修改');
        }
        $compareInfo = $compareObj->toArray();
        if ($compareInfo['bill_status'] == 'B' || $compareInfo['bill_status'] == 'C') {
            check(false, '已经提交的数据无法修改');
        }
        $otherCompare = Compare::where('inquiry_id', $base['inquiry_id'])->whereIn('bill_status', ['B', 'C'])->first();
        if (isset($otherCompare['id'])) {
            check(false, '比价单已存在提交比价单无需重复提交');
        }
        $admin = Auth::guard('admin')->user();
        $compareData = [
            'bill_no' => !empty($base['bill_no']) ? $base['bill_no'] : null,
            'inquiry_title' => !empty($base['inquiry_title']) ? $base['inquiry_title'] : null,
            'inquiry_no' => $inquiryInfo['bill_no'],
            'related_no' => !empty($base['related_no']) ? $base['related_no'] : null,
            'org_id' => !empty($base['org_id']) ? $base['org_id'] : 1,
            'bill_date' => !empty($base['bill_date']) ? $base['bill_date'] : date('Y-m-d H:i:s'),
            'end_date' => !empty($base['end_date']) ? $base['end_date'] : null,
            'sup_scope' => !empty($base['sup_scope']) ? $base['sup_scope'] : null,
            'open_type' => !empty($base['open_type']) ? $base['open_type'] : null,
            'person_id' => !empty($base['person_id']) ? $base['person_id'] : null,
            'phone' => !empty($base['phone']) ? $base['phone'] : null,
            'remark' => !empty($base['remark']) ? $base['remark'] : null,
            'total_inquiry' => !empty($base['total_inquiry']) ? $base['total_inquiry'] : null,
            'opinion_tag' => !empty($base['opinion_tag']) ? $base['opinion_tag'] : null,
            'date_from' => !empty($base['date_from']) ? $base['date_from'] : null,
            'bill_status' => !empty($base['bill_status']) ? $base['bill_status'] : null,
            'date_to' => !empty($base['date_to']) ? $base['date_to'] : null,
            'settle_type_id' => !empty($base['settle_type_id']) ? $base['settle_type_id'] : null,
            'payment_terms' => !empty($base['payment_terms']) ? $base['payment_terms'] : null,
            'other_pay_terms_info' => !empty($base['other_pay_terms_info']) ? $base['other_pay_terms_info'] : null,
            'tax_cal_type' => !empty($base['tax_cal_type']) ? $base['tax_cal_type'] : null,
            'deli_date' => !empty($base['deli_date']) ? $base['deli_date'] : null,
            'deli_addr' => !empty($base['deli_addr']) ? $base['deli_addr'] : null,
            'curr_id' => !empty($base['curr_id']) ? $base['curr_id'] : null,
            'inv_type' => !empty($base['inv_type']) ? $base['inv_type'] : null,
            'inquiry_id' => !empty($base['inquiry_id']) ? $base['inquiry_id'] : null,
            'annual' => !empty($base['annual']) ? $base['annual'] : 'N',
            'project_manager' => !empty($base['project_manager']) ? $base['project_manager'] : null,
            //'required_level' => !empty($base['required_level']) ? $base['required_level'] : null,
            //'required_cat' => !empty($base['required_cat']) ? $base['required_cat'] : null,
            //'is_filter' => !empty($base['is_filter']) ? $base['is_filter'] : null,
            // 'text_field' => !empty($base['text_field']) ? $base['text_field'] : null,
            //'check_box_field' => !empty($base['check_box_field']) ? $base['check_box_field'] : null,
            'sum_tax_amount' => !empty($base['sum_tax_amount']) ? $base['sum_tax_amount'] : null,
            'delivery_date' => !empty($base['delivery_date']) ? $base['delivery_date'] : null,
            'warranty_period' => !empty($base['warranty_period']) ? $base['warranty_period'] : null,
            //'business_type_id' => !empty($base['business_type_id']) ? $base['business_type_id'] : null,
            //'source' => !empty($base['source']) ? $base['source'] : null,
            'audit_status' => $base['bill_status'] == 'B' ? 'REVIEW' : 'DRAFT',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id,
        ];
        $flag = Compare::where('id', $compareId)->update($compareData);
        (new AttachRepo)->updateData($compareId, $request);
        (new CompareQuoteRepo)->updateData($compareId, $request);
        (new EntryRepo)->updateData($compareId, $request);
        if (!empty($base['bill_status']) && $base['bill_status'] === 'B') {
            (new CompareAuditRepo)->auditStart($compareId);
        }
        return $flag;
    }

    /**
     * @param int $inquiryId
     * @param Request $request
     * 
     * @return array
     */
    public function add($request) {
        $base = $request->base;
        $admin = Auth::guard('admin')->user();
        $inquiryObj = Inquiry::where('id', $base['inquiry_id'])->first();
        if (empty($inquiryObj)) {
            check(false, '询单不存在');
        }
        $inquiryInfo = $inquiryObj->toArray();
        if ($inquiryInfo['biz_status'] == 'E') {
            check(false, '询单已终止，无法新增');
        }
        $compareInfo = Compare::where('inquiry_id', $base['inquiry_id'])->whereIn('bill_status', ['B', 'C'])->first();
        if (isset($compareInfo['id'])) {
            check(false, '比价单已存在无需重复提交');
        }
        $compareData = [
            'bill_no' => !empty($base['bill_no']) ? $base['bill_no'] : null,
            'inquiry_title' => !empty($base['inquiry_title']) ? $base['inquiry_title'] : null,
            'inquiry_no' => $inquiryInfo['bill_no'],
            'related_no' => !empty($base['related_no']) ? $base['related_no'] : null,
            'org_id' => !empty($base['org_id']) ? $base['org_id'] : 1,
            'bill_date' => !empty($base['bill_date']) ? $base['bill_date'] : date('Y-m-d H:i:s'),
            'deli_date' => !empty($base['deli_date']) ? $base['deli_date'] : null,
            'deli_addr' => !empty($base['deli_addr']) ? $base['deli_addr'] : null,
            'end_date' => !empty($base['end_date']) ? $base['end_date'] : null,
            'date_from' => !empty($base['date_from']) ? $base['date_from'] : null,
            'date_to' => !empty($base['date_to']) ? $base['date_to'] : null,
            'sup_scope' => !empty($base['sup_scope']) ? $base['sup_scope'] : null,
            'open_type' => !empty($base['open_type']) ? $base['open_type'] : null,
            'person_id' => !empty($base['person_id']) ? $base['person_id'] : null,
            'phone' => !empty($base['phone']) ? $base['phone'] : null,
            'remark' => !empty($base['remark']) ? $base['remark'] : null,
            'total_inquiry' => !empty($base['total_inquiry']) ? $base['total_inquiry'] : null,
            'opinion_tag' => !empty($base['opinion_tag']) ? $base['opinion_tag'] : null,
            'bill_status' => !empty($base['bill_status']) ? $base['bill_status'] : null,
            'settle_type_id' => !empty($base['settle_type_id']) ? $base['settle_type_id'] : null,
            'payment_terms' => !empty($base['payment_terms']) ? $base['payment_terms'] : null,
            'other_pay_terms_info' => !empty($base['other_pay_terms_info']) ? $base['other_pay_terms_info'] : null,
            'tax_cal_type' => !empty($base['tax_cal_type']) ? $base['tax_cal_type'] : null,
            'curr_id' => !empty($base['curr_id']) ? $base['curr_id'] : null,
            'inv_type' => !empty($base['inv_type']) ? $base['inv_type'] : null,
            'inquiry_id' => !empty($base['inquiry_id']) ? $base['inquiry_id'] : null,
            'annual' => !empty($base['annual']) ? $base['annual'] : 'N',
            'project_manager' => !empty($base['project_manager']) ? $base['project_manager'] : null,
            //'required_level' => !empty($base['required_level']) ? $base['required_level'] : null,
            //'required_cat' => !empty($base['required_cat']) ? $base['required_cat'] : null,
            //'is_filter' => !empty($base['is_filter']) ? $base['is_filter'] : null,
            // 'text_field' => !empty($base['text_field']) ? $base['text_field'] : null,
            //'check_box_field' => !empty($base['check_box_field']) ? $base['check_box_field'] : null,
            //'quoted_num' => !empty($base['quoted_num']) ? $base['quoted_num'] : null,
            'delivery_date' => !empty($base['delivery_date']) ? $base['delivery_date'] : null,
            'warranty_period' => !empty($base['warranty_period']) ? $base['warranty_period'] : null,
            //'business_type_id' => !empty($base['business_type_id']) ? $base['business_type_id'] : null,
            'sum_tax_amount' => !empty($base['sum_tax_amount']) ? $base['sum_tax_amount'] : null,
            'audit_status' => $base['bill_status'] == 'B' ? 'REVIEW' : 'DRAFT',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $admin->user_id,
        ];
        $compareId = Compare::insertGetId($compareData);
        (new AttachRepo)->updateData($compareId, $request);
        (new CompareQuoteRepo)->updateData($compareId, $request);
        (new EntryRepo())->updateData($compareId, $request);
        if (!empty($base['bill_status']) && $base['bill_status'] === 'B') {
            (new CompareAuditRepo)->auditStart($compareId);
        }
        return $compareId;
    }

    /**
     * 删除
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return Compare::whereIn('id', $ids)->where('bill_status', 'A')->update(['deleted_flag' => 'Y']);
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        $query->where('compare.deleted_flag', 'N');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $user = (new \App\Common\Models\User)->getTable();
                $q->where('compare.inquiry_title', 'like', '%' . $keyword . '%')
                        ->orWhere('compare.bill_no', 'like', '%' . $keyword . '%')
                        ->orWhere('compare.inquiry_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->bill_status)) {
            $billStatus = $request->bill_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('compare.bill_status', $billStatusies);
        }
        if (!empty($request->bill_no)) {
            $query->where('compare.bill_no', 'like', '%' . trim($request->bill_no) . '%');
        }
        if (!empty($request->related_no)) {
            $query->where('inquiry.related_no', 'like', '%' . trim($request->related_no) . '%');
        }
        if (!empty($request->inquiry_no)) {
            $query->where('compare.inquiry_no', 'like', '%' . trim($request->inquiry_no) . '%');
        }
        if (!empty($request->inquiry_title)) {
            $query->where('compare.inquiry_title', 'like', '%' . trim($request->inquiry_title) . '%');
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('compare.bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('compare.bill_date', $createAts);
        }
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getCompareNo($newNumber) {
        $prefix = 'BJ';
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
        return $this->createSerialNo(1, $prefix, '');
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
        return $prefix . $time . $pad;
    }

    public function getBillStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '保存';
            case 'B':
                return '已提交';
            case 'C':
                return '已审核';
        }
    }

    public function getBizStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '报价中';
            case 'B':
                return '已开标';
            case 'C':
                return '已定标';
            case 'D':
                return '已执行';
            case 'E':
                return '已终止';
        }
    }

    public function getBizStatusList() {
        return [
            'A' => '报价中',
            'B' => '已开标',
            'C' => '已定标',
            'D' => '已执行',
            'E' => '已终止',
        ];
    }

    public function getSupScopeText($supScope) {
        switch (strtoupper($supScope)) {
            case '1':
                return '所有供应商';
            case '2':
                return '指定供应商';
        }
    }

    public function getSupScopeList() {
        return [
            '1' => '所有供应商',
            '2' => '指定供应商',
        ];
    }

    public function getOpenTypeText($openType) {
        switch (strtoupper($openType)) {
            case '1':
                return '报价即可见自动开标';
            case '4':
                return '报价即可见手工开标';
            case '2':
                return '到截止时间自动开标';
            case '3':
                return '到截止时间手动开标';
        }
    }

    public function getOpenTypeList() {
        return [
            '2' => '到截止时间自动开标',
            '3' => '到截止时间手动开标',
        ];
    }

    public function getBillStatusList() {
        return [
            'A' => '保存',
            'B' => '已提交',
            'C' => '已审核',
        ];
    }

    public function notice($compareId, $inquiryId) {
        $inquiry = Inquiry::where('id', $inquiryId)
                ->first()
                ->toArray();
        $compare = Compare::where('id', $compareId)->first()->toArray();
        $inquiryEntry = (new InquiryEntry)->getTable();
        $compareEntry = (new Entry)->getTable();
        $compareTbale = (new Compare())->getTable();
        $quoteEntry = (new QuoteEntry())->getTable();
        $compareQuote = (new CompareQuote())->getTable();
        $materialList = InquiryEntry::selectRaw('ie.material_name,ie.material_desc,'
                        . 'ie.inquire_qty,ie.precision,ie.inquiry_unit_id,ce.supplier_id')
                ->from($inquiryEntry . ' as ie')
                ->join($compareTbale . ' as c', function($join) {
                    $join->on('c.inquiry_id', '=', 'ie.inquiry_id');
                })
                ->join($compareEntry . ' as ce', function($join) {
                    $join->on('ce.compare_id', '=', 'c.id')
                    ->on('ce.material_id', '=', 'ie.id');
                })
                ->where('ce.adopt_flag', 'true')
                ->where('ce.compare_id', $compareId)
                ->where('ie.deleted_flag', 'N')
                ->where('ie.inquiry_id', $inquiryId)
                ->get()
                ->toArray();

        $obj = Purchaser::where('id', $inquiry['org_id'])
                ->where('deleted_flag', 'N')
                ->where('enable', 1)
                ->first();
        (new PaycondRepo)->setPaycond($inquiry, 'payment_terms');
        (new UnitRepo)->setUnits($materialList, 'inquiry_unit_id', 'inquiry_unit_name');
        (new SupplierBaseRepo)->setSuppliers($materialList, 'supplier_id', 'supplier_name');
        $user = User::where('user_id', $inquiry['person_id'])->where('deleted_flag', 'N')->first();
        $content = [
            'inquiry_title' => $inquiry['title'],
            'bill_no' => $compare['inquiry_no'],
            'bill_date' => date('Y-m-d', strtotime($inquiry['bill_date'])),
            'deli_date' => $inquiry['deli_date'],
            'deli_addr' => $inquiry['deli_addr'],
            'due_date' => date('Y-m-d', strtotime($inquiry['end_date'])),
            'paycond_name' => $inquiry['paycond_name'],
            'other_pay_terms' => $inquiry['other_pay_terms_info'],
            'person_name' => $user->realname,
            'person_phone' => $user->phone,
            'materials' => $materialList,
            'materials' => $materialList,
        ];
        $data = [
            'biz_type' => 'A',
            'due_date' => date('Y-m-d', strtotime($inquiry['end_date'])),
            'org_id' => $inquiry['org_id'],
            'bill_no' => (new NoticeManageRepo)->getNoticeNo(),
            'src_bill_id' => $inquiryId,
            'src_bill_type' => 'sou_compare',
            'bill_status' => 'C',
            'bill_type_id' => 0,
            'src_bill_no' => '比价单：' . $compare['bill_no'],
            'sup_scope' => $inquiry['sup_scope'],
            'title' => $inquiry['title'] . '项目结果',
        ];
        $data['content'] = view('tpl.inquiry_result', $content)->toHtml();
        $data['content_array'] = $content;
        return $data;
    }

    public function setCompareIds(&$list, $filed = 'inquiry_id', $fieldKey = 'compare_id') {
        if (empty($list)) {
            return;
        }
        $inquiryIds = [];
        foreach ($list as &$item) {
            $item[$fieldKey] = null;
            $item['compare_status'] = null;
            $inquiryIds[] = $item[$filed];
        }
        $compareList = Compare::select('id', 'inquiry_id', 'bill_status')
                ->whereIn('inquiry_id', $inquiryIds)
                ->where('deleted_flag', 'N')
//          ->where('bill_status', 'C')
                ->get()
                ->toArray();



        if (empty($compareList)) {
            return;
        }
        $compareArr = array_column($compareList, 'id', 'inquiry_id');
        $compareStatusArr = array_column($compareList, 'bill_status', 'inquiry_id');
        foreach ($list as &$item) {
            $item[$fieldKey] = !empty($compareArr[$item[$filed]]) ? $compareArr[$item[$filed]] : null;
            $item['compare_status'] = !empty($compareStatusArr[$item[$filed]]) ?
                    $compareStatusArr[$item[$filed]] : null;
        }
    }

    public function setCompareId(&$data, $filed = 'inquiry_id', $fieldKey = 'compare_id') {
        if (empty($data)) {
            return;
        }
        $data[$fieldKey] = null;
        $inquiryId = $data[$filed];
        $data[$fieldKey] = Compare::select('id', 'inquiry_id')
                ->where('inquiry_id', $inquiryId)
                ->where('deleted_flag', 'N')
                ->where('bill_status', 'C')
                ->value('id');
    }

}
