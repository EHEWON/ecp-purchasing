<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    BidBill\BidBill,
    BidBill\BidBillSupplier,
    BidBill\Sub,
    Message,
    MessageReceiver,
    Supplier AS BaseSupplier,
    User,
    UserSupplier
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    BidBill\AttachRepo AS BBAttachRepo,
    BidBill\EntryRepo AS BBEntryRepo,
    BidBill\QuoteAttachRepo,
    BidBill\QuoteRepo AS BBQuoteRepo,
    BidBill\SubRepo AS BBSubRepo,
    BidBill\SupplierRepo AS BBSupplierRepo,
    CurrencyRepo,
    PaycondRepo,
    SettleMentTypeRepo,
    Supplier\BidBillPayRepo,
    UserRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BidBillRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'name',
        'biz_status',
        'bill_status',
        'org_id',
        'bill_date',
        'enroll_date',
        'open_date',
        'result_date',
        'created_at',
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
        $this->model = new BidBill();
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

        check($supplier->enable == '1', '供应商已被禁用');
        $this->source = $supplier->source;
        $this->supplierName = $supplier->name;
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        if (!in_array($action, $this->filter)) {
            check($supplier->status === 'APPROVED', '供应商没有准入');
        }
    }

    public function getEntryStatusText() {
        
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
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
        $supplierTable = (new BidBillSupplier)->getTable();
        $supplierId = $this->supplierId;
        $filed = 'bb.id,bb.bill_no,bs.entry_status,bb.biz_type,bs.enroll_date as quoted_at,deposit_flag,'
                . 'bb.name,bb.bill_status,bb.org_id,bb.bill_date,bb.bill_status,bb.bid_status,bb.bid_number,bb.cash_deposit as sure_amount,'
                . 'bb.enroll_date,bb.open_date,bb.result_date,bb.sum_tax_amount,bb.created_by,bb.created_at,bb.reduce_type,'
                . 'bb.quotation_trend,bb.reducepct,bs.allow_bid';

        $query = $this->model
                ->from($this->model->getTable() . ' as bb')
                ->selectRaw($filed)
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
            $join->on('bb.id', '=', 'bs.bid_bill_id')
            ->where('bs.supplier_id', $supplierId);
        });
        $this->getWhere($query, $request);
        $query->where(function($q) {
                    $q->whereRaw('(bs.id IS NOT NULL AND biz_type=2)')
                    ->orWhere(function($q1) {
                        $q1->where('biz_type', 1);
                    });
                })
                ->where('bb.bill_status', 'C');
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query
                ->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new BidBillPayRepo)->setPayStatus($data, $supplierId);
        $bbsupplierRepor = new BBSupplierRepo();
        $time = date('Y-m-d H:i:s');
        foreach ($data as &$item) {
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            $item['bid_status_name'] = $this->getBidStatusText($item['bid_status']);
            if ($item['enroll_date'] > $time && ((empty($item['entry_status']) && $item['biz_type'] == '1') || $item['entry_status'] === 'T' )) {
                $item['entry_status'] = 'T';
                $item['entry_status_name'] = '待报名';
            } elseif ($item['enroll_date'] < $time && ((empty($item['entry_status']) && $item['biz_type'] == '1') || $item['entry_status'] === 'T' )) {
                $item['entry_status'] = 'WCY';
                $item['entry_status_name'] = '未参与';
            } else {
                $item['entry_status_name'] = $bbsupplierRepor->getEntryStatusText($item['entry_status']);
            }
            $item['access_status'] = 'APPROVED';
            $item['reducepct'] = number_format($item['reducepct'], 2, '.', '');
            $item['sure_amount'] = number_format($item['sure_amount'], 2, '.', '');
            $item['reduce_type_name'] = $this->getReduceTypeText($item['reduce_type']);
            $item['quotation_trend_name'] = $this->getQuotationTrendText($item['quotation_trend']);
        }
        (new BBSubRepo)->setSubs($data, 'id');
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * 待报名中的竞价
     * @param Request $request
     * @return type
     */
    public function todoQuote(Request $request) {
        $supplierTable = (new BidBillSupplier)->getTable();
        $supplierId = $this->supplierId;
        $query = $this->model
                ->from($this->model->getTable() . ' as bb')
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
            $join->on('bb.id', '=', 'bs.bid_bill_id')
            ->where('bs.supplier_id', $supplierId);
        });
        $this->getWhere($query, $request);
        $query->where(function($q) {
                    $q->whereRaw('(bs.id IS NOT NULL AND bb.biz_type=\'2\' AND bs.entry_status=\'T\')')
                    ->orWhere(function($q1) {
                        $q1->where('bb.biz_type', '1')
                        ->whereNull('bs.entry_status');
                    });
                })
                ->where('bb.bill_status', 'C')
                ->where('bb.enroll_date', '>', date('Y-m-d H:i:s'));
        return $query->count();
    }

    /**
     * 待缴费的竞价
     * @param Request $request
     * @return type
     */
    public function todoPay(Request $request) {
        $supplierTable = (new BidBillSupplier)->getTable();
        $supplierId = $this->supplierId;
        $query = $this->model
                ->from($this->model->getTable() . ' as bb')
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
            $join->on('bb.id', '=', 'bs.bid_bill_id')
            ->where('bs.supplier_id', $supplierId);
        });
        $this->getWhere($query, $request);
        $query->where(function($q) {
                    $q->whereRaw('(bs.id IS NOT NULL AND biz_type=2)')
                    ->orWhere(function($q1) {
                        $q1->where('biz_type', 1);
                    });
                })
                ->where('bb.bill_status', 'C')
                ->where('bs.entry_status', 'L');
        return $query->count();
    }

    /**
     * 待缴费的竞价
     * @param Request $request
     * @return type
     */
    public function todoHandeling(Request $request) {
        $supplierTable = (new BidBillSupplier)->getTable();
        $supplierId = $this->supplierId;
        $query = $this->model
                ->from($this->model->getTable() . ' as bb')
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
            $join->on('bb.id', '=', 'bs.bid_bill_id')
            ->where('bs.supplier_id', $supplierId);
        });
        $this->getWhere($query, $request);
        $query->where(function($q) {
                    $q->whereRaw('(bs.id IS NOT NULL AND biz_type=2)')
                    ->orWhere(function($q1) {
                        $q1->where('biz_type', 1);
                    });
                })
                ->where('bb.bill_status', 'C')
                ->where('bs.entry_status', 'M');
        return $query->count();
    }

    public function getTotal(Request $request) {
        $supplierTable = (new BidBillSupplier)->getTable();
        $supplierId = $this->supplierId;
        $query = $this->model
                ->from($this->model->getTable() . ' as bb')
                ->leftJoin($supplierTable . ' as bs', function($join)use($supplierId) {
            $join->on('bb.id', '=', 'bs.bid_bill_id')
            ->where('bs.supplier_id', $supplierId);
        });
        $this->getWhere($query, $request);
        $query->whereRaw('bs.id IS NOT NULL AND bs.`entry_status` NOT IN(\'T\',\'WCY\',\'N\')')
                ->where('bb.bill_status', 'C');
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
        $data = [];
        $suppliers = (new BBSupplierRepo)->getList($id, $this->supplierId);
        $base = $object->toArray();
        $base['cash_deposit'] = !empty($base['cash_deposit']) ? number_format($base['cash_deposit'], 2, '.', '') : null;
        $base['sure_amount'] = $base['cash_deposit'];
        $base['max_amount'] = !empty($base['max_amount']) ? number_format($base['max_amount'], 2, '.', '') : null;
        $base['min_amount'] = !empty($base['min_amount']) ? number_format($base['min_amount'], 2, '.', '') : null;
        $base['bill_status_name'] = $this->getBillStatusText($base['bill_status']);
        $base['bid_status_name'] = $this->getBidStatusText($base['bid_status']);
        $base['biz_type_name'] = $this->getBizTypeText($base['biz_type']);
        $base['check_type_name'] = $this->getCheckTypeText($base['check_type']);
        $base['quote_mode_name'] = $this->getQuoteModeText($base['quote_mode']);
        $base['biz_model_name'] = $this->getBizModelText($base['biz_model']);
        $base['reduce_type_name'] = $this->getReduceTypeText($base['reduce_type']);
        $base['tax_type_name'] = $this->getTaxTypeText($base['tax_type']);
        $base['inv_type_name'] = $this->getInvtypeText($base['inv_type']);
        $base['certificate_name'] = $this->getCertificateText($base['certificate']);
        $base['quotation_trend_name'] = $this->getQuotationTrendText($base['quotation_trend']);
        $bbsupplierRepor = new BBSupplierRepo();
        $supplier = !empty($suppliers[0]) ? $suppliers[0] : [];
        $base['entry_status'] = isset($supplier['entry_status']) ? $supplier['entry_status'] : '';
        $time = date('Y-m-d H:i:s');
        if ($base['enroll_date'] > $time && ((empty($base['entry_status']) && $base['biz_type'] == '1') || $supplier['entry_status'] === 'T' )) {
            $base['entry_status'] = 'T';
            $base['entry_status_name'] = '待报名';
        } elseif ($base['enroll_date'] < $time && ((empty($base['entry_status']) && $base['biz_type'] == '1') || $supplier['entry_status'] === 'T' )) {
            $base['entry_status'] = 'WCY';
            $base['entry_status_name'] = '未参与';
        } else {
            $base['entry_status_name'] = $bbsupplierRepor->getEntryStatusText($base['entry_status']);
        }
        if (!empty($supplier) && $supplier['allow_bid'] == '1') {
            $base['allow_bid'] = '1';
        } else {
            $base['allow_bid'] = '0';
        }
        (new UserRepo)->setUser($base, 'person_id', 'person_name');
        (new UserRepo)->setUser($base, 'created_by', 'created_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settle_type_id', 'settle_type_name');
        (new PaycondRepo)->setPaycond($base, 'pay_cond_id', ['pay_cond_name' => 'name']);
        (new CurrencyRepo)->setCurrency($base, 'curr_id', 'curr_name');

        $base['category_name'] = $this->getCategoryText($base['required_category']);
        $data['base'] = $base;
        $base['inv_type_name'] = $this->getInvtypeText($base['inv_type']);
        $data['attachs'] = (new BBAttachRepo)->getList($id);
        $data['entrys'] = (new BBEntryRepo)->getList($id);
        $data['quotes'] = (new BBQuoteRepo())->getList($id);
        (new QuoteAttachRepo)->setQuoteAttachs($data['quotes']);
        $data['suppliers'] = $suppliers;
        $data['sub'] = (new BBSubRepo)->info($id);
        return $data;
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

        if (!empty($request->bid_status)) {
            $bidStatus = $request->bid_status;
            $bidStatusies = is_array($bidStatus) ? $bidStatus : explode(',', trim($bidStatus));
            $query->whereIn('bb.bid_status', $bidStatusies);
        }

        if (!empty($request->bill_status)) {
            $billStatus = $request->bill_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('bb.bill_status', $billStatusies);
        }
        if (!empty($request->entry_status)) {
            $entryStatus = $request->entry_status;
            switch ($entryStatus) {
                case 'WCY':
                    $query->where(function($q) {
                        $q->where('bb.enroll_date', '<', date('Y-m-d H:i:s'))
                                ->whereRaw('((bs.entry_status IS NULL AND bb.biz_type=1)'
                                        . ' OR bs.entry_status=\'N\''
                                        . ' OR bs.entry_status=\'WCY\''
                                        . ' OR bs.entry_status=\'T\')');
                    });
                    break;
                case 'T':
                    $query->where(function($q) {
                        $q->where('bb.enroll_date', '>', date('Y-m-d H:i:s'))
                                ->whereRaw('((bs.entry_status IS NULL AND bb.biz_type=1)'
                                        . ' OR bs.entry_status=\'T\')');
                    });
                    break;
                default:
                    $query->where('bs.entry_status', $entryStatus);
                    break;
            }
        }



        if (!empty($request->check_type)) {
            $query->where('bb.check_type', trim($request->check_type));
        }
        if (!empty($request->reduce_type)) {
            $query->where('bb.reduce_type', trim($request->reduce_type));
        }

        if (!empty($request->name)) {
            $query->where('bb.name', 'like', '%' . trim($request->name) . '%');
        }
        if (!empty($request->biz_type)) {
            $query->where('bb.biz_type', trim($request->biz_type));
        }

        if (!empty($request->statusies)) {
            $query->whereIn('bb.bid_status', $request->statusies);
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('bb.bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bb.bill_date', $createAts);
        }
        if (!empty($request->opentype)) {
            $open_dateAts = $this->getTimeByType($request->opentype);
            $query->whereBetween('bb.open_date', $open_dateAts);
        } elseif (!empty($request->open_date)) {
            $opentime = $request->open_date;
            $opentimeAts = is_array($opentime) ? $opentime : explode(',', $opentime);
            !empty($opentimeAts[1]) ? $opentimeAts[1] = date('Y-m-d 23:59:59', strtotime($opentimeAts[1])) : $opentimeAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bb.open_date', $opentimeAts);
        }
        if (!empty($request->enrolltype)) {
            $enroll_dateAts = $this->getTimeByType($request->enrolltype);
            $query->whereBetween('bb.enroll_date', $enroll_dateAts);
        } elseif (!empty($request->enroll_date)) {
            $enroll_time = $request->enroll_date;
            $enrollAts = is_array($enroll_time) ? $enroll_time : explode(',', $enroll_time);
            !empty($enrollAts[1]) ? $enrollAts[1] = date('Y-m-d 23:59:59', strtotime($enrollAts[1])) : $enrollAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bb.enroll_date', $enrollAts);
        }
        if (!empty($request->resulttype)) {
            $resultAts = $this->getTimeByType($request->resulttype);
            $query->whereBetween('bb.result_date', $resultAts);
        } elseif (!empty($request->result_date)) {
            $result_time = $request->result_date;
            $resultAts = is_array($result_time) ? $result_time : explode(',', $result_time);
            !empty($resultAts[1]) ? $resultAts[1] = date('Y-m-d 23:59:59', strtotime($resultAts[1])) : $resultAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bb.result_date', $resultAts);
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

    public function getBizTypeText($status) {
        switch (strtoupper($status)) {
            case '1':
                return '所有供应商';
            case '2':
                return '指定供应商';
        }
    }

    public function getCheckTypeText($status) {
        switch (strtoupper($status)) {
            case '1':
                return '资格预审';
            case '2':
                return '资格后审';
            case '3':
                return '资格免审';
        }
    }

    public function getBidStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '报名中';
            case 'B':
                return '已资审';
            case 'C':
                return '竞价中';
            case 'D':
                return '评标中';
            case 'E':
                return '已定标';
            case 'F':
                return '已执行';
            case 'G':
                return '已终止';
            case 'I':
                return '报名截止';
            case 'H':
                return '已暂停';
            case 'J':
                return '定标审批中';
            case 'K':
                return '已收保证金';
            case 'L':
                return '待收保证金';
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
     * 报价趋势
     * @param type $status
     * @return string
     */
    public function getQuotationTrendText($status) {
        switch (strtoupper($status)) {
            case '1':
                return '不限制';
            case '2':
                return '仅允许降价';
            case '3':
                return '仅允许加价';
        }
    }

    /**
     * 每次降价方式
     * @param type $status
     * @return string
     */
    public function getReduceTypeText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '按比例(%)';
            case 'B':
                return '按金额';
        }
    }

    /**
     * 竞价模式
     * @param type $quoteMode
     * @return string
     */
    public function getQuoteModeText($quoteMode) {
        switch (strtoupper($quoteMode)) {
            case '1':
                return '总额竞价';
            case '2':
                return '行项目竞价';
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
        $bidBill = BidBill::where('id', $id)
                ->select('enroll_date', 'bill_status', 'bid_status', 'name', 'deposit_flag', 'check_type', 'org_id', 'id', 'person_id')
                ->first();
        $supplier = BaseSupplier::where('id', $supplierId)
                ->selectRaw('id,status,deleted_flag,enable,source')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        check($supplier->status === 'APPROVED', '供应商没有通过审核');
        check($supplier->enable == '1', '供应商已被禁用');

        if ($bidBill->enroll_date < date('Y-m-d H:i:s')) {
            check(false, '已过报名截止时间');
        }
        if ($bidBill->bill_status != 'C') {
            check(false, '单据状态状态不是已审核');
        }
        if ($bidBill->bid_status != 'A') {
            check(false, '项目状态不是报名中');
        }

        $entryStatus = $bidBill->check_type == '1' ? 'A' : ( $bidBill->deposit_flag === 'Y' ? 'L' : 'Y');
        $isupplier = BidBillSupplier::where('bid_bill_id', $id)
                ->where('supplier_id', $supplierId)
                ->first();
        if ($isupplier) {
            $isupplierId = $isupplier['id'];
            if (!in_array($isupplier['entry_status'], ['N', 'WCY'])) {
                // check(false, '不能重复报名');
            }
        } else {
            $isupplierId = '';
        }
        $flag = empty($isupplierId) ? BidBillSupplier::insert([
                    'supplier_id' => $supplierId,
                    'enroll_date' => date('Y-m-d H:i:s'),
                    'bid_bill_id' => $id,
                    'entry_status' => $entryStatus,
                    'enroll_id' => $this->userId,
                    'note' => $request->note,
                    'remark' => $request->note,
                    'contact_name' => $admin->real_name,
                    'contact_phone' => $admin->phone,
                    'contact_email' => $admin->email,
                    'created_at' => date('Y-m-d H:i:s')
                ]) : BidBillSupplier::where('id', $isupplierId)->update([
                    'entry_status' => $entryStatus,
                    'enroll_date' => date('Y-m-d H:i:s'),
                    'note' => $request->note,
                    'remark' => $request->note,
                    'enroll_id' => $this->userId,
                    'contact_name' => $admin->real_name,
                    'contact_phone' => $admin->phone,
                    'contact_email' => $admin->email,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->userId,
        ]);
        $enrollNumber = BidBillSupplier::where('bid_bill_id', $id)
                ->select('entry_status', 'Y')
                ->count();
        Sub::where('bid_bill_id', $id)
                ->update([
                    'enroll_number' => $enrollNumber,
        ]);

        $this->sendSigns($bidBill->toArray(), $id);
        return $flag;
    }

    public function sendSigns($bidBillData, $bidBillId) {
        if ($bidBillData['bill_status'] !== 'C') {
            return;
        }
        $email = User::where('user_id', $bidBillData['person_id'])->value('email');
        if (empty($email)) {
            return;
        }
        $this->sendSignMail($bidBillData, $email);
        $this->sendSignMessage($bidBillData, $bidBillId);
    }

    public function sendSignMessage($bidBillData, $bidBillId) {
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/bidding/BiddingDetails?id=' . $bidBillId,
                    'sender_id' => $bidBillData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '竞价报名结果通知',
                    'message' => '【' . $bidBillData['name'] . '】已有【' . $this->supplierName . '】报名，快速处理',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $data = [
            'message_id' => $messageId,
            'receiver_id' => $bidBillData['person_id'],
            'supplier_id' => $this->supplierId,
            'org_id' => $bidBillData['org_id'],
            'read_flag' => 'N',
            'created_at' => date('Y-m-d H:i:s')
        ];
        MessageReceiver::insert($data);
    }

    public function sendSignMail($bidBillData, $email) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'bidBillData' => $bidBillData,
            'email' => $email,
            'supplierName' => $this->supplierName,
                ], 'BIDBILL_SIGN'));
    }

    /**
     * 不报名参加竞价
     * @param int $id
     * @param Request $request
     */
    public function unSignUp(int $id, Request $request) {
        $supplierId = $this->supplierId;
        $admin = $this->admin;
        $bidBill = BidBill::where('id', $id)
                ->select('enroll_date', 'bill_status', 'bid_status', 'deposit_flag', 'check_type', 'org_id')
                ->first();
        $supplier = BaseSupplier::where('id', $supplierId)
                ->selectRaw('id,status,deleted_flag,enable,source')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        check($supplier->status === 'APPROVED', '供应商没有准入');
        check($supplier->enable == '1', '供应商已被禁用');

        if ($bidBill->enroll_date < date('Y-m-d H:i:s')) {
            check(false, '已过报名截止时间');
        }
        if ($bidBill->bill_status != 'C') {
            check(false, '单据状态状态不是已审核');
        }
        if ($bidBill->bid_status != 'A') {
            check(false, '项目状态不是报名中');
        }
        $isupplierId = BidBillSupplier::where('bid_bill_id', $id)
                ->where('supplier_id', $supplierId)
                ->value('id');
        return empty($isupplierId) ? BidBillSupplier::insert([
                    'supplier_id' => $supplierId,
                    'enroll_id' => $this->userId,
                    'enroll_date' => date('Y-m-d H:i:s'),
                    'bid_bill_id' => $id,
                    'entry_status' => 'N',
                    'note' => $request->note,
                    'remark' => $request->note,
                    'contact_name' => $admin->real_name,
                    'contact_phone' => $admin->phone,
                    'contact_email' => $admin->email,
                    'created_at' => date('Y-m-d H:i:s')
                ]) : BidBillSupplier::where('id', $isupplierId)->update([
                    'entry_status' => 'N',
                    'note' => $request->note,
                    'remark' => $request->note,
                    'enroll_id' => $this->userId,
                    'enroll_date' => date('Y-m-d H:i:s'),
                    'contact_name' => $admin->real_name,
                    'contact_phone' => $admin->phone,
                    'contact_email' => $admin->email,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->userId,
        ]);
    }

}
