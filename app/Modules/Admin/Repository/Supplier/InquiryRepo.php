<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Inquiry\Inquiry,
    Inquiry\Supplier,
    Quote\Quote,
    Supplier AS BaseSupplier,
    UserSupplier
};
use App\Modules\Admin\Repository\{
    CurrencyRepo,
    Inquiry\AttachRepo,
    Inquiry\EntryRepo,
    Inquiry\InquiryRepo as RfqRepo,
    Inquiry\SupplierRepo as ISupplierRepo,
    SettleMentTypeRepo,
    Supplier\SupplierRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryRepo extends Repository {

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
    protected $supplierId = null;
    protected $source = null;
    protected $admin = null;
    protected $userId = null;
    protected $filter = [
        'statistics',
        'todo',
    ];

    public function __construct() {
        $this->model = new Inquiry();
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
                ->selectRaw('id,status,deleted_flag,enable,source')
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
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? 'i.' . trim($request->sort) : 'i.created_at';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'i.created_at') {
            $query->orderBy('i.created_at', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = 'i.id,i.bill_no,i.title,i.stopped_reason,i.stopped_at,'
    . 'i.biz_status,i.bill_status,i.org_id,i.bill_date,i.sup_scope,i.turns,i.open_type,i.tax_cal_type,'
    . 'i.end_date,i.person_id,is.entry_status,is.supplier_biz_status,is.supplier_id') {
        $supplierId = $this->supplierId;
        $supplierTable = (new Supplier)->getTable();
        $supplierQuery = Supplier::from($supplierTable . ' as sup')
                ->selectRaw('sup.inquiry_id,max(sup.entry_turns) as max_turns')
                ->where('sup.deleted_flag', 'N')
                ->where('sup.supplier_id', $supplierId)
                ->orderBy('sup.entry_turns', 'DESC')
                ->groupBy('sup.inquiry_id');

        $query = $this->model
                ->from($this->model->getTable() . ' as i')
                ->leftJoinSub($supplierQuery, 'max', function ($join) {
                    $join->on('i.id', '=', 'max.inquiry_id');
                })
                ->leftJoin($supplierTable . ' as is', function($join)use($supplierId) {
            $join->on('i.id', '=', 'is.inquiry_id')
            ->on('is.entry_turns', 'max.max_turns')
            ->where('is.supplier_id', $supplierId)
            ->where('is.deleted_flag', 'N');
        });
        $query->where(function($q) {
                    $q->whereRaw('`is`.id IS NOT NULL')
                    ->where('i.sup_scope', '2')
                    ->orWhere(function($q1) {
                        $q1->where('i.sup_scope', 1);
                    });
                })
                ->where('i.bill_status', 'C')
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
        $rfqRepo = new RfqRepo();
        $iSupplierRepo = new ISupplierRepo();
        $time = date('Y-m-d H:i:s');
        foreach ($data as &$item) {
            if ($item['biz_status'] === 'E') {
                $item['supplier_biz_status'] = 'E';
                $item['supplier_entry_status_name'] = '已终止';
            } elseif ($item['end_date'] > $time && ((empty($item['supplier_biz_status']) && $item['sup_scope'] == '1') || $item['supplier_biz_status'] === 'A' )) {
                $item['supplier_biz_status'] = 'A';
                $item['supplier_entry_status_name'] = '待报价';
            } elseif ($item['end_date'] < $time &&
                    ((empty($item['supplier_biz_status']) && $item['sup_scope'] == '1') || in_array($item['supplier_biz_status'], ['C', 'D', 'A']))) {
                $item['supplier_biz_status'] = 'D';
                $item['supplier_entry_status_name'] = '未参与';
            } elseif (!empty($item['supplier_biz_status']) && $item['supplier_biz_status'] == 'E') {
                $item['supplier_entry_status_name'] = '已终止';
            } elseif ($item['supplier_biz_status'] === 'D') {
                $item['supplier_entry_status_name'] = $iSupplierRepo->getBizStatusText($item['supplier_biz_status']);
            } else {
                $item['supplier_entry_status_name'] = $iSupplierRepo->getEntryStatusText($item['entry_status']);
            }
            $item['access_status'] = 'APPROVED';
            $item['supplier_id'] = (string) $item['supplier_id'];
            $item['bill_date'] = date('Y-m-d', strtotime($item['bill_date']));
            $item['bill_status_name'] = $rfqRepo->getBillStatusText($item['bill_status']);
            $item['biz_status_name'] = $rfqRepo->getBizStatusText($item['biz_status']);
            $item['sup_scope_name'] = $rfqRepo->getSupScopeText($item['sup_scope']);
            $item['open_type_name'] = $rfqRepo->getOpenTypeText($item['open_type']);
            $item['tax_cal_type_name'] = $rfqRepo->getTaxCalTypeText($item['tax_cal_type']);
        }
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        (new QuoteRepo)->setQuoteIds($data, $supplierId);
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function toBeQuoted() {
        $supplierId = $this->supplierId;
        $supplierTable = (new Supplier)->getTable();
        $supplierQuery = Supplier::from($supplierTable . ' as sup')
                ->selectRaw('sup.inquiry_id,max(sup.entry_turns) as max_turns')
                ->where('sup.deleted_flag', 'N')
                ->where('sup.supplier_id', $supplierId)
                ->orderBy('sup.entry_turns', 'DESC')
                ->groupBy('sup.inquiry_id');

        $query = $this->model
                ->from($this->model->getTable() . ' as i')
                ->leftJoinSub($supplierQuery, 'max', function ($join) {
                    $join->on('i.id', '=', 'max.inquiry_id');
                })
                ->leftJoin($supplierTable . ' as is', function($join)use($supplierId) {
            $join->on('i.id', '=', 'is.inquiry_id')
            ->on('is.entry_turns', 'max.max_turns')
            ->where('is.supplier_id', $supplierId)
            ->where('is.deleted_flag', 'N');
        });
        $query->where(function($q) {
                    $q->whereRaw('is.id IS NOT NULL')
                    ->where('i.sup_scope', '2')
                    ->orWhere(function($q1) {
                        $q1->where('i.sup_scope', 1);
                    });
                })
                ->where('i.bill_status', 'C');
        $query->where(function($q) {
            $q->where('i.end_date', '>', date('Y-m-d H:i:s'))
                    ->whereRaw('((is.supplier_biz_status IS NULL AND i.sup_scope=1) '
                            . ' OR is.supplier_biz_status=\'A\')');
        });
        return $query->count();
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {
        $supplierId = $this->supplierId;
        $query = $this->model->selectRaw('id,bill_no,related_no,title,bill_date,deli_date,deli_addr,'
                . 'org_id,person_id,phone,settle_type_id,curr_id,person_id,tax_cal_type,stopped_reason,stopped_at,'
                . 'loc_curr_id,inv_type,sup_scope,end_date,date_from,date_to,bill_status,open_type,'
                . 'payment_terms,settlement_method,total_inquiry,remark,biz_status,turns,other_pay_terms,'
                . 'other_pay_terms_info');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = [];
        $base = $object->toArray();
        $rfqRepo = new RfqRepo();
        $iSupplierRepo = new ISupplierRepo();


        $time = date('Y-m-d H:i:s');
        $base['deli_date'] = !empty($base['deli_date']) ? date('Y-m-d', strtotime($base['deli_date'])) : null;
        $base['bill_date'] = date('Y-m-d', strtotime($base['bill_date']));
        $base['inv_type_name'] = $rfqRepo->getInvtypeText($base['inv_type']);
        $base['bill_status_name'] = $rfqRepo->getBillStatusText($base['bill_status']);
        $base['biz_status_name'] = $rfqRepo->getBizStatusText($base['biz_status']);
        $base['sup_scope_name'] = $rfqRepo->getSupScopeText($base['sup_scope']);
        $base['open_type_name'] = $rfqRepo->getOpenTypeText($base['open_type']);
        $base['tax_cal_type_name'] = $rfqRepo->getTaxCalTypeText($base['tax_cal_type']);
        $base['turns_name'] = $rfqRepo->getTurnsText($base['turns']);
        (new UserRepo)->setUser($base, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrency($base, 'curr_id', 'curr_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settle_type_id', 'settle_type_name');
        (new SupplierRepo)->setSupplierStatus($base, $supplierId);
        $quote = Quote::select('turns', 'id')
                ->where('inquiry_id', $id)
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->orderBy('turns', 'DESC')
                ->first();
        $base['quote_id'] = !empty($quote) ? $quote->id : null;
        $base['quote_turns'] = !empty($quote) ? $quote->turns : null;

        $entryTurns = $base['turns'];
        $data['attachs'] = (new AttachRepo)->getList($id);
        $data['entrys'] = (new EntryRepo)->getList($id, $entryTurns);
        $data['supplier'] = (new SupplierRepo)->getList($id, $supplierId, $entryTurns, $base['end_date']);
        if (count($data['supplier']) === 0) {
            $data['base'] = $base;
            return $data;
        }
        $length = count($data['supplier']);
        $supplier = $data['supplier'][$length - 1];
        if ($base['biz_status'] === 'E') {
            $base['supplier_biz_status'] = 'E';
            $base['supplier_entry_status_name'] = '已终止';
        } elseif ($base['end_date'] > $time && ((empty($supplier['supplier_biz_status']) && $base['sup_scope'] == '1') || $supplier['supplier_biz_status'] === 'A' )) {
            $base['supplier_biz_status'] = 'A';
            $base['supplier_entry_status_name'] = '待报价';
        } elseif ($base['end_date'] < $time //报价截止时间小于当前时间
//          && empty($supplier['entry_status']) //未报价
                && ((empty($supplier['supplier_biz_status']) && $base['sup_scope'] == '1') || in_array($supplier['supplier_biz_status'], ['C', 'D', 'A']))) {
            $base['supplier_biz_status'] = 'D';
            $base['supplier_entry_status_name'] = '未参与';
        } elseif (!empty($supplier['supplier_biz_status']) && $supplier['supplier_biz_status'] == 'E') {
            $base['supplier_biz_status'] = 'E';
            $base['supplier_entry_status_name'] = '已终止';
        } elseif ($supplier['supplier_biz_status'] === 'D') {
            $base['supplier_entry_status_name'] = $iSupplierRepo->getBizStatusText($supplier['supplier_biz_status']);
        } else {
            $base['supplier_entry_status_name'] = $iSupplierRepo->getEntryStatusText($supplier['entry_status']);
        }
        $data['base'] = $base;
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
                $purchaser = (new \App\Common\Models\Purchaser)->getTable();
                $q->where('i.title', 'like', '%' . $keyword . '%')
                        ->orWhere('i.bill_no', 'like', '%' . $keyword . '%')
                        ->orWhereRaw('EXISTS(SELECT p.id FROM ' . $purchaser
                                . ' as p WHERE p.name like \'%' . $keyword . '%\''
                                . ' AND p.deleted_flag=\'N\' AND p.id=i.org_id)');
            });
        }
        if (!empty($request->biz_status)) {
            $bizStatusies = is_array($request->biz_status) ? $request->biz_status : explode(',', trim($request->biz_status));
            $query->whereIn('i.biz_status', $bizStatusies);
        }
        if (!empty($request->bill_status)) {
            $billStatusies = is_array($request->bill_status) ? $request->bill_status : explode(',', trim($request->bill_status));
            $query->whereIn('i.bill_status', $billStatusies);
        }
        if (!empty($request->bill_no)) {
            $query->where('i.bill_no', 'like', '%' . trim($request->bill_no) . '%');
        }
        if (!empty($request->entry_status)) {
            switch ($request->entry_status) {
                case 'A':
                case 'B':
                case 'C':
                case 'D':
                case 'E':
                    $query->where('is.entry_status', trim($request->entry_status));
                    break;
                case 'WCY'://未参与
                    $query->where(function($q) {
                        $q->where('i.end_date', '<', date('Y-m-d H:i:s'))
                                ->whereRaw('((is.supplier_biz_status IS NULL AND i.sup_scope=1)'
                                        . ' OR is.supplier_biz_status=\'A\''
                                        . ' OR is.supplier_biz_status=\'C\''
                                        . ' OR is.supplier_biz_status=\'D\')');
                    });
                    break;
                case 'DBJ'://待报价
                    $query->where(function($q) {
                        $q->where('i.end_date', '>', date('Y-m-d H:i:s'))
                                ->whereRaw('((is.supplier_biz_status IS NULL AND i.sup_scope=1) '
                                        . ' OR is.supplier_biz_status=\'A\')');
                    });
                    break;
                case 'YZZ'://未参与                    
                    $query->where('is.supplier_biz_status', 'E');
                    break;
            }
        }



        if (!empty($request->sup_scope)) {
            $query->where('i.sup_scope', trim($request->sup_scope));
        }
        if (!empty($request->title)) {
            $query->where('i.title', 'like', '%' . trim($request->title) . '%');
        }
        if (!empty($request->open_type)) {
            $query->where('i.open_type', trim($request->open_type));
        }
        if (!empty($request->statusies)) {
            $query->whereIn('i.biz_status', $request->statusies);
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('i.bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('i.bill_date', $createAts);
        }
        if (!empty($request->endtype)) {
            $createAts = $this->getTimeByType($request->endtype);
            $query->whereBetween('end_date', $createAts);
        } elseif (!empty($request->endtime)) {
            $createtime = $request->endtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('end_date', $createAts);
        }
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

    public function getBillStatusList() {
        return [
            'A' => '保存',
            'B' => '已提交',
            'C' => '已审核',
        ];
    }

    /**
     * 不报价
     * @param int $id
     */
    public function unQuote(int $id) {
        $supplierId = $this->supplierId;
        $admin = $this->admin;
        $inquiry = Inquiry::where('id', $id)->select('end_date', 'bill_status', 'biz_status', 'turns')->first();
        $turns = $inquiry->turns;
        if ($inquiry->end_date < date('Y-m-d H:i:s')) {
            check(false, '已过报价有效期');
        }
        if ($inquiry->bill_status != 'C') {
            check(false, '单据状态状态不是已审核');
        }
        if ($inquiry->biz_status != 'A') {
            check(false, '项目状态不是报价中');
        }

        $isupplierId = Supplier::where('inquiry_id', $id)
                ->where('supplier_id', $supplierId)
                ->value('id');
        empty($isupplierId) ? Supplier::insert([
                            'supplier_id' => $supplierId,
                            'inquiry_id' => $id,
                            'entry_status' => 'F',
                            'dead_line' => $inquiry->end_date,
                            'entry_count' => 1,
                            'entry_turns' => $turns,
                            'supplier_biz_status' => 'D',
                            'contact_name' => $admin->real_name,
                            'contact_phone' => $admin->phone,
                            'contact_email' => $admin->email,
                            'created_by' => $this->userId,
                            'created_at' => date('Y-m-d H:i:s')
                        ]) : Supplier::where('id', $isupplierId)->update([
                            'entry_status' => 'F',
                            'entry_turns' => $turns,
                            'dead_line' => $inquiry->end_date,
                            'supplier_biz_status' => 'D',
                            'updated_at' => date('Y-m-d H:i:s'),
                            'updated_by' => $this->userId
        ]);
    }

    /**
     * 不报价
     * @param int $id
     */
    public function quote(int $id) {
        $supplierId = $this->supplierId;
        $admin = $this->admin;
        $inquiry = Inquiry::where('id', $id)->select('end_date', 'bill_status', 'biz_status')->first();
        $turns = $inquiry->turns;
        if ($inquiry->end_date < date('Y-m-d H:i:s')) {
            check(false, '已过报价有效期');
        }
        if ($inquiry->bill_status != 'C') {
            check(false, '单据状态状态不是已审核');
        }
        if ($inquiry->biz_status != 'A') {
            check(false, '项目状态不是报价中');
        }
        $isupplierId = Supplier::where('inquiry_id', $id)
                ->where('supplier_id', $supplierId)
                ->value('id');
        empty($isupplierId) ? Supplier::insert([
                            'supplier_id' => $supplierId,
                            'quote_date' => date('Y-m-d H:i:s'),
                            'quote_date' => date('Y-m-d H:i:s'),
                            'inquiry_id' => $id,
                            'entry_status' => '',
                            'entry_turns' => $turns,
                            'supplier_biz_status' => 'A',
                            'entry_turns' => $turns,
                            'contact_name' => $admin->real_name,
                            'contact_phone' => $admin->phone,
                            'contact_email' => $admin->email,
                            'created_at' => date('Y-m-d H:i:s')
                        ]) : Supplier::where('id', $isupplierId)->update([
                            'entry_status' => '',
                            'supplier_biz_status' => 'A',
                            'entry_turns' => $turns,
                            'contact_name' => $admin->real_name,
                            'contact_phone' => $admin->phone,
                            'contact_email' => $admin->email,
                            'updated_at' => date('Y-m-d H:i:s'),
                            'updated_by' => $this->userId,
        ]);
    }

}
