<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Inquiry\Inquiry,
    Inquiry\Supplier,
    Message,
    MessageReceiver,
    Quote\Quote,
    Quote\QuoteSub,
    Supplier AS BaseSupplier,
    User,
    UserSupplier
};
use App\Modules\Admin\Repository\{
    CurrencyRepo,
    Inquiry\InquiryRepo,
    Inquiry\SubRepo,
    Inquiry\SupplierRepo AS ISupplierRepo,
    PaycondRepo,
    SendLogRepo,
    SettleMentTypeRepo,
    SupplierBaseRepo,
    SupplierContactRepo,
    Supplier\AttachRepo,
    Supplier\QuoteEntryRepo,
    Supplier\SupplierRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB
};
use PhpOffice\PhpSpreadsheet\Style\{
    Alignment,
    Border,
    Font
};

class QuoteRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'inquiry_title',
        'biz_status',
        'bill_status',
        'bill_date',
        'end_date',
        'person_id',
    ];

    public function __construct() {
        $this->model = new Quote();
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
            $query->orderBy('bill_date', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = 'id,bill_no,org_id,'
    . 'inquiry_title,bill_date,sum_tax_amount,biz_status,'
    . 'bill_status,curr_id,supplier_id,loc_curr_id,inquiry_no') {
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
        $query = $this->model
                ->selectRaw($filed);
        $query->where('supplier_id', $supplierId);
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
            $item['biz_status_name'] = $this->getBizStatusText($item['biz_status']);
            list($item['status_name'], $item['status']) = $this->getStatusText($item['bill_status'], $item['biz_status']);
        }
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrencys($data, 'curr_id', ['curr_number' => 'number', 'curr_sign' => 'sign']);
        (new CurrencyRepo)->setCurrencys($data, 'loc_curr_id', ['loc_curr_number' => 'number', 'loc_curr_sign' => 'sign']);
        (new SupplierBaseRepo)->setSuppliers($data, 'supplier_id', 'supplier_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function todoQuoted() {
        $supplierId = $this->getPSupplierId();
        return Quote::where('bill_status', 'A')
                        ->where('supplier_id', $supplierId)
                        ->count();
    }

    public function getTotal(Request $request) {
        $supplierId = $this->getPSupplierId();
        $query = Quote::where('supplier_id', $supplierId)->where('bill_status', 'C');
        $this->getWhere($query, $request);
        return $query->count();
    }

    public function getTotalByDate(Request $request) {
        $supplierId = $this->getPSupplierId();
        $query = Quote::where('supplier_id', $supplierId)
                ->where('bill_status', 'C');

        $this->getWhere($query, $request);
        switch (strtolower($request->createtype)) {
            case 'today':
                $object = $query->selectRaw('SUBSTR(bill_date,12,2)as billdate,count(id) AS num')
                        ->groupBy(DB::Raw('SUBSTR(bill_date,12,2)'))
                        ->orderBy('billdate', 'ASC')
                        ->get();
                break;
            default :
                $object = $query->selectRaw('SUBSTR(bill_date,1,10)as billdate,count(id) AS num')
                        ->groupBy(DB::Raw('SUBSTR(bill_date,1,10)'))
                        ->orderBy('billdate', 'ASC')
                        ->get();
                break;
        }
        switch (strtolower($request->createtype)) {
            case 'past_week':
                $defualt = [];
                for ($day = 0; $day <= 6; $day++) {
                    $defualt[date('Y-m-d', strtotime(($day - 6) . ' days'))] = 0;
                }
                break;
            case 'today':
                $ath = date('H') - 1;
                for ($hour = 0; $hour <= $ath; $hour++) {
                    $defualt[date('H', strtotime(($hour - $ath) . ' hours'))] = 0;
                }
                break;
            case 'this_month':
                $atd = date('d') - 1;
                for ($day = 0; $day <= $atd; $day++) {
                    $defualt[date('Y-m-d', strtotime(($day - $atd) . ' days'))] = 0;
                }

                break;
            default:
                $createtime = $request->createtime;
                $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
                $origin = date_create($createAts[0]);
                $target = date_create($createAts[1]);
                $curDate = date_create(date('Y-m-d'));
                $interval = date_diff($origin, $target);
                $interval1 = date_diff($curDate, $target);
                $atd = $interval->format('%a');
                $curDay = $interval1->format('%a');
                for ($day = $curDay; $day <= $atd - $curDay; $day++) {
                    $defualt[date('Y-m-d', strtotime(($day - $atd) . ' days'))] = 0;
                }
                break;
        }

        $ret = [];
        if (empty($object)) {
            foreach ($defualt as $date => $num) {
                $ret[] = ['date' => $date, 'num' => $num];
            }
            return $ret;
        }
        $list = $object->toArray();
        foreach ($list as $item) {
            $defualt[$item['billdate']] = $item['num'];
        }
        foreach ($defualt as $date => $num) {
            $ret[] = [
                'date' => strtolower($request->createtype) == 'today' ? $date : substr($date, 5, 5),
                'week' => strtolower($request->createtype) == 'today' ? $this->getHour($date) : $this->getWeek($date),
                'num' => $num];
        }
        return $ret;
    }

    public function getHour($date) {
        switch ($date) {
            case '00':
                return '上午0点';
            case '01':
                return '上午1点';
            case '02':
                return '上午2点';
            case '03':
                return '上午3点';
            case '04':
                return '上午4点';
            case '05':
                return '上午5点';
            case '06':
                return '上午6点';
            case '08':
                return '上午0点';
            case '08':
                return '上午1点';
            case '09':
                return '上午2点';
            case '10':
                return '上午3点';
            case '11':
                return '上午4点';
            case '12':
                return '下午0点';
            case '13':
                return '下午1点';
            case '14':
                return '下午2点';
            case '15':
                return '下午3点';
            case '16':
                return '下午4点';
            case '17':
                return '下午5点';
            case '18':
                return '下午6点';
            case '19':
                return '下午7点';
            case '20':
                return '下午8点';
            case '21':
                return '下午9点';
            case '22':
                return '下午10点';
            case '23':
                return '下午11点';
        }
    }

    public function getWeek($date) {
        switch (date('w', strtotime($date))) {
            case '0':
                return '周日';
            case '1':
                return '周一';
            case '2':
                return '周二';
            case '3':
                return '周三';
            case '4':
                return '周四';
            case '5':
                return '周五';
            case '6':
                return '周六';
        }
    }

    public function getAdoptTotal(Request $request) {
        $supplierId = $this->getPSupplierId();
        $query = Quote::where('bill_status', 'C')
                ->whereIn('biz_status', ['C', 'D'])
                ->where('supplier_id', $supplierId);
        $this->getWhere($query, $request);
        return $query->count();
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function infoByInquiryId($inquiryId) {
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
        $inquiry = Inquiry::where('id', $inquiryId)->first();
        $query = $this->model->selectRaw('id,bill_no,inquiry_title,bill_date,deli_date,deli_addr,'
                . 'org_id,person_id,settle_type_id,curr_id,person_id,contact_name,contact_phone,contact_email,'
                . 'loc_curr_id,inv_type,end_date,date_from,date_to,bill_status,sum_tax_amount,other_pay_terms_info,'
                . 'payment_terms,settlement_method,total_inquiry,remark,biz_status,date_from,date_to,'
                . 'tax_cal_type,turns,turns_count,delivery_date,inv_type,inquiry_no,supplier_id,inquiry_id');
        $query->where('inquiry_id', $inquiryId);
        $query->where('turns', !empty($inquiry->turns) ? $inquiry->turns : 1);
        $query->where('supplier_id', $supplierId);
        $object = $query->first();
        if (empty($object)) {
            return $this->getDefaultQuote($inquiry, $supplierId, 1, $inquiry->edn_date);
        }
        $data = [];
        $base = $object->toArray();
        $quoteId = $base['id'];
        $base['bill_status_name'] = $this->getBillStatusText($base['bill_status']);
        $base['biz_status_name'] = $this->getBizStatusText($base['biz_status']);
        (new UserRepo)->setUser($base, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrency($base, 'curr_id', 'curr_name');
        (new CurrencyRepo)->setCurrency($base, 'loc_curr_id', 'loc_curr_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settle_type_id', 'settle_type_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settlement_method', 'settlement_method_name');
        (new PaycondRepo)->setPaycond($base, 'payment_terms', ['payment_terms_name' => 'id']);
        list($base['status_name'], $base['status']) = $this->getStatusText($base['bill_status'], $base['biz_status']);
        $base['tax_cal_type_name'] = (new InquiryRepo)->getTaxCalTypeText($base['tax_cal_type']);
        $base['inv_type_name'] = (new InquiryRepo)->getInvtypeText($base['inv_type']);
        $base['sum_tax_amount'] = number_format($base['sum_tax_amount'], 2, '.', ',');
        $data['base'] = $base;
        $data['attachs'] = (new AttachRepo)->getList($quoteId);
        $data['entrys'] = (new QuoteEntryRepo)->getList($quoteId);
        $data['supplier'] = (new SupplierRepo)->getList($inquiryId, $supplierId);
        return $data;
    }

    public function getDefaultQuote($inquiry, $supplierId) {
        if (empty($inquiry) || empty($supplierId)) {
            return [];
        }
        $data = [];
        $contact = (new SupplierContactRepo)->getDefaultContact($supplierId);
        $base = [
            'id' => null,
            'bill_no' => null,
            'inquiry_title' => $inquiry->title,
            'bill_date' => null,
            'deli_date' => null,
            'deli_addr' => null,
            'org_id' => $inquiry->org_id,
            'person_id' => $inquiry->person_id,
            'settle_type_id' => $inquiry->settle_type_id,
            'curr_id' => $inquiry->curr_id,
            'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
            'contact_phone' => !empty($contact['phone']) ? $contact['phone'] : '',
            'contact_email' => !empty($contact['email']) ? $contact['email'] : '',
            'loc_curr_id' => null,
            'inv_type' => $inquiry->inv_type,
            'end_date' => $inquiry->end_date,
            'date_from' => $inquiry->date_from,
            'date_to' => $inquiry->date_to,
            'bill_status' => null,
            'sum_tax_amount' => null,
            'payment_terms' => $inquiry->payment_terms,
            'other_pay_terms_info' => $inquiry->other_pay_terms_info,
            'settlement_method' => $inquiry->settlement_method,
            'total_inquiry' => $inquiry->total_inquiry,
            'remark' => null,
            'biz_status' => null,
            'status' => 'A',
            'status_name' => '待报价',
            'tax_cal_type' => $inquiry->tax_cal_type,
            'turns' => $inquiry->turns,
            'turns_count' => $inquiry->turns,
            'delivery_date' => $inquiry->turns,
            'inquiry_no' => $inquiry->bill_no,
            'inquiry_id' => $inquiry->id,
            'supplier_id' => $supplierId,
        ];
        $base['bill_status_name'] = $this->getBillStatusText($base['bill_status']);
        $base['biz_status_name'] = $this->getBizStatusText($base['biz_status']);
        (new UserRepo)->setUser($base, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrency($base, 'curr_id', 'curr_name');
        (new CurrencyRepo)->setCurrency($base, 'loc_curr_id', 'loc_curr_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settle_type_id', 'settle_type_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settlement_method', 'settlement_method_name');
        (new PaycondRepo)->setPaycond($base, 'payment_terms', ['payment_terms_name' => 'id']);
        $base['tax_cal_type_name'] = (new InquiryRepo)->getTaxCalTypeText($base['tax_cal_type']);
        $base['inv_type_name'] = (new InquiryRepo)->getInvtypeText($base['inv_type']);
        $data['base'] = $base;
        $data['attachs'] = [];
        $data['entrys'] = (new QuoteEntryRepo)->getDefaultEntrys($inquiry->id);
        return $data;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function listByInquiryId($inquiryId) {
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
        $query = $this->model
                ->selectRaw('id,bill_no,org_id,'
                . 'inquiry_title,bill_date,sum_tax_amount,biz_status,'
                . 'bill_status,curr_id,supplier_id,loc_curr_id,inquiry_no');
        $query->where('inquiry_id', $inquiryId);
        $query->where('supplier_id', $supplierId);
        $query->orderBy('created_at', 'DESC');
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            $item['biz_status_name'] = $this->getBizStatusText($item['biz_status']);
            list($item['status_name'], $item['status']) = $this->getStatusText($item['bill_status'], $item['biz_status']);
        }
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrencys($data, 'curr_id', ['curr_number' => 'number', 'curr_sign' => 'sign']);
        (new CurrencyRepo)->setCurrencys($data, 'loc_curr_id', ['loc_curr_number' => 'number', 'loc_curr_sign' => 'sign']);
        return $data;
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
            check(false, '您没有关联供应商');
        }
        $supplier = BaseSupplier::where('id', $supplierId)
                ->selectRaw('id,status,deleted_flag,enable')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        check($supplier->status === 'APPROVED', '供应商没有准入');
        check($supplier->enable == '1', '供应商已被禁用');
        $query = $this->model->selectRaw('id,bill_no,inquiry_title as title,inquiry_title ,bill_date,deli_date,deli_addr,'
                . 'org_id,person_id,settle_type_id,curr_id,person_id,contact_name,contact_phone,contact_email,'
                . 'loc_curr_id,inv_type,end_date,date_from,date_to,bill_status,sum_tax_amount,other_pay_terms_info,'
                . 'payment_terms,settlement_method,total_inquiry,remark,biz_status,date_from,date_to,'
                . 'tax_cal_type,turns,turns_count,delivery_date,inv_type,inquiry_no,supplier_id,inquiry_id');
        $query->where('id', $id);
        $query->where('supplier_id', $supplierId);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = [];
        $inquiryId = $object->inquiry_id;
        $base = $object->toArray();
        $iSupplierRepo = new ISupplierRepo();
        $base['bill_status_name'] = $this->getBillStatusText($base['bill_status']);
        $base['biz_status_name'] = $this->getBizStatusText($base['biz_status']);
        list($base['status_name'], $base['status']) = $this->getStatusText($base['bill_status'], $base['biz_status']);
        $time = date('Y-m-d H:i:s');

        if ($base['end_date'] > $time && $base['bill_status'] !== 'C') {
            $base['supplier_entry_status_name'] = '待报价';
            $base['supplier_entry_status'] = 'DBJ';
        } elseif ($base['end_date'] < $time && $base['bill_status'] !== 'C') {
            $base['supplier_entry_status_name'] = '未参与';
            $base['supplier_entry_status'] = 'WCY';
        } elseif (!empty($base['bill_status']) && ($base['bill_status'] == 'Z' || $base['bill_status'] == 'D')) {
            $base['supplier_entry_status_name'] = '已终止';
            $base['supplier_entry_status'] = 'YZZ';
        } else {
            $base['supplier_entry_status'] = $base['biz_status'];
            $base['supplier_entry_status_name'] = $iSupplierRepo->getEntryStatusText($base['biz_status']);
        }
        (new UserRepo)->setUser($base, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrency($base, 'curr_id', 'curr_name');
        (new CurrencyRepo)->setCurrency($base, 'loc_curr_id', 'loc_curr_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settle_type_id', 'settle_type_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settlement_method', 'settlement_method_name');
        (new PaycondRepo)->setPaycond($base, 'payment_terms', ['payment_terms_name' => 'id']);
        $base['tax_cal_type_name'] = (new InquiryRepo)->getTaxCalTypeText($base['tax_cal_type']);
        $base['inv_type_name'] = (new InquiryRepo)->getInvtypeText($base['inv_type']);
        $base['turns_name'] = (new InquiryRepo)->getTurnsText($base['turns']);
        $base['phone'] = Inquiry::where('id', $inquiryId)->value('phone');
        $data['base'] = $base;
        $data['attachs'] = (new AttachRepo)->getList($id);
        $data['entrys'] = (new QuoteEntryRepo)->getList($id);
        $data['supplier'] = (new SupplierRepo)->getList($inquiryId, $supplierId, 1, $base['end_date']);
        return $data;
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        $query->whereRaw('deleted_flag=\'N\'');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $purchaser = (new \App\Common\Models\Purchaser)->getTable();
                $q->where('inquiry_title', 'like', '%' . $keyword . '%')
                        ->orWhere('bill_no', 'like', '%' . $keyword . '%')
                        ->orWhere('inquiry_no', 'like', '%' . $keyword . '%')
                        ->orWhereRaw('EXISTS(SELECT p.id FROM ' . $purchaser
                                . ' as p WHERE p.name like \'%' . $keyword . '%\''
                                . ' AND p.deleted_flag=\'N\' AND p.id=quote.org_id)');
            });
        }
        if (!empty($request->bill_no)) {
            $query->where('bill_no', 'like', '%' . trim($request->bill_no) . '%');
        }
        if (!empty($request->title)) {
            $query->where('inquiry_title', 'like', '%' . trim($request->title) . '%');
        }
        if (!empty($request->statusies)) {
            $query->whereIn('biz_status', $request->statusies);
        }
        if (!empty($request->status)) {
            switch (strtoupper($request->status)) {
                case 'A':
                    $query->where('bill_status', 'A');
                    break;
                case 'B':
                    $query->where('bill_status', 'C');
                    $query->where('biz_status', 'A');
                    break;
                case 'CA':
                case 'CB':
                    $query->where('bill_status', 'C');
                    $query->where('biz_status', 'B');
                    break;
                case 'CC':
                    $query->where('bill_status', 'C');
                    $query->where('biz_status', 'C');
                    break;
                case 'CD':
                    $query->where('bill_status', 'C');
                    $query->where('biz_status', 'D');
                    break;
                case 'CCD':
                    $query->where('bill_status', 'C');
                    $query->where('biz_status', ['C', 'D']);
                    break;
                case 'CE':
                    $query->where('bill_status', 'C');
                    $query->where('biz_status', 'E');
                    break;
                case 'D':
                    $query->where('bill_status', 'D');
                    break;
                case 'Z':
                    $query->where('bill_status', 'E');
                    break;
            }
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bill_date', $createAts);
        }
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getQuoteNo() {
        $prefix = 'BJ';
        $qurey = $this->model->selectRaw('*');
        $billNo = $qurey
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

    /**
     * @param int $quoteId
     * @param Request $request
     * 
     * @return array
     */
    public function edited($quoteId, Request $request) {
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
        $inquiry = Inquiry::where('id', $base['inquiry_id'])->first();
        if (empty($inquiry)) {
            check(false, '询价单不存在');
        }
        if ($inquiry->bill_status === 'A') {
            check(false, '询单已被撤销');
        }
        check($inquiry->bid_status !== 'E', '已终止的询价但不能报价');
        if ($inquiry->turns > 1) {
            $entryTurns = Supplier::where('inquiry_id', $base['inquiry_id'])
                    ->where('supplier_id', $supplierId)
                    ->orderBy('entry_turns', 'DESC')
                    ->value('entry_turns');
            check($inquiry->turns === $entryTurns, '您不是多轮报价邀请供应商,不能报价');
        }
        if (!empty($inquiry->end_date) && $inquiry->end_date < date('Y-m-d H:i:s')) {
            check(false, '已过报价有效期');
        }
        $quoteStatus = Quote::where('id', $quoteId)->where('deleted_flag', 'N')
                ->value('bill_status');
        $entryCount = Quote::where('inquiry_id', $base['inquiry_id'])
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->where('bill_status', 'C')
                ->count();

        $contact = (new SupplierContactRepo)->getDefaultContact($supplierId);
        $quoteData = [
            'inquiry_id' => $base['inquiry_id'],
            'inquiry_title' => !empty($inquiry['title']) ? $inquiry['title'] : null,
            'inquiry_no' => !empty($inquiry['bill_no']) ? $inquiry['bill_no'] : null,
//            'bill_date' => $inquiry['bill_date'],
            'biz_type' => $inquiry['biz_type'],
            'end_date' => $inquiry['end_date'],
            'req_org_id' => $inquiry['req_org_id'],
            'org_id' => $inquiry['org_id'],
            'rcv_org_id' => $inquiry['rcv_org_id'],
            'settle_org_id' => $inquiry['settle_org_id'],
            'pay_org_id' => $inquiry['pay_org_id'],
            'person_id' => $inquiry['person_id'],
            'date_from' => $inquiry['date_from'],
            'date_to' => $inquiry['date_to'],
            'settle_type_id' => !empty($base['settle_type_id']) ? $base['settle_type_id'] : null,
            'curr_id' => $inquiry['curr_id'],
            'loc_curr_id' => $inquiry['loc_curr_id'],
            'exch_type_id' => $inquiry['exch_type_id'],
            'exch_rate' => $inquiry['exch_rate'],
            'tax_type' => $inquiry['tax_type'],
            'cfm_status' => $inquiry['cfm_status'],
            'biz_partner_id' => $inquiry['biz_partner_id'],
            'deli_date' => $inquiry['deli_date'],
            'deli_addr' => $inquiry['deli_addr'],
            'bill_type_id' => $inquiry['bill_type_id'],
            'inv_type' => $inquiry['inv_type'],
            'total_inquiry' => $inquiry['total_inquiry'],
            'turns' => $inquiry['turns'],
            'turns_count' => $entryCount + 1,
            'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
            'contact_phone' => !empty($contact['phone']) ? $contact['phone'] : '',
            'contact_email' => !empty($contact['email']) ? $contact['email'] : '',
            'sup_curr_type' => $inquiry['sup_curr_type'],
            'rate_date' => $inquiry['rate_date'],
            'remark' => $inquiry['remark'],
            'inquiry_title' => $inquiry['inquiry_title'],
            'payment_terms' => !empty($base['payment_terms']) ? $base['payment_terms'] : null,
            'settlement_method' => !empty($base['settlement_method']) ? $base['settlement_method'] : null,
            'other_pay_terms_info' => !empty($base['other_pay_terms_info']) ? $base['other_pay_terms_info'] : null,
            'settlement_cur' => $inquiry['settlement_cur'],
            'tax_cal_type' => $inquiry['tax_cal_type'],
            'exchange_rate_date' => $inquiry['exchange_rate_date'],
            'delivery_date' => $inquiry['delivery_date'],
            'text_field' => $inquiry['text_field'],
            'warranty_period' => $inquiry['warranty_period'],
            'business_type_id' => $inquiry['business_type_id'],
            'source' => $inquiry['source'],
            'delivery_date' => !empty($base['delivery_date']) ? $base['delivery_date'] : null,
            'warranty_period' => !empty($base['warranty_period']) ? $base['warranty_period'] : null,
            'bill_status' => !empty($base['bill_status']) ? $base['bill_status'] : 'A',
            'biz_status' => !empty($base['bill_status']) && $base['bill_status'] === 'C' ? 'A' : '',
            'supplier_id' => $supplierId,
            'source' => !empty($base['source']) ? $base['source'] : null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id,
        ];
        if ($quoteStatus === 'A') {
            $flag = Quote::where('id', $quoteId)->update($quoteData);
        } else {
            $quoteData['bill_date'] = date('Y-m-d H:i:s');
            $quoteData['bill_no'] = $this->getQuoteNo();
            $flag = $quoteId = Quote::insertGetId($quoteData);
        }

        if (!empty($base['bill_status']) && $base['bill_status'] === 'C') {
            Supplier::upsert(['inquiry_id' => $base['inquiry_id'],
                'supplier_id' => $supplierId,
                'quote_date' => date('Y-m-d H:i:s'),
                'quote_id' => $quoteId,
                'quoter_id' => $admin->user_id,
                'entry_status' => 'A',
                'supplier_biz_status' => 'B',
                'entry_turns' => $inquiry['turns'],
                'entry_count' => $entryCount + 1,
                'dead_line' => $inquiry['end_date'],
                'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
                'contact_phone' => !empty($contact['phone']) ? $contact['phone'] : '',
                'contact_email' => !empty($contact['email']) ? $contact['email'] : '',
                'created_by' => $admin->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_by' => $admin->user_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_flag' => 'N',
                    ], ['supplier_id', 'inquiry_id', 'entry_turns'], ['quote_date',
                'quote_date',
                'quote_id',
                'quoter_id',
                'entry_status',
                'supplier_biz_status',
                'entry_turns',
                'entry_count',
                'dead_line',
                'contact_name',
                'contact_phone',
                'contact_email',
            ]);
        }
        $subId = QuoteSub::where('quote_id', $quoteId)->value('id');
        !empty($subId) ? QuoteSub::where('quote_id', $quoteId)
                                ->update([
                                    'quote_id' => $quoteId,
                                    'modifier_id' => $admin->user_id,
                                    'modify_time' => date('Y-m-d H:i:s'),
                                    'updated_by' => $admin->user_id,
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ]) : QuoteSub::insert([
                            'quote_id' => $quoteId,
                            'created_by' => $admin->user_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'creator_id' => $admin->user_id,
                            'create_time' => date('Y-m-d H:i:s'),
        ]);
        (new AttachRepo)->updateData($quoteId, $request);
        list($sumAmount, $sumTax, $sumTaxAmount, $sumQty) = (new QuoteEntryRepo())->updateData($base['inquiry_id'], $quoteId, $request);
        Quote::where('id', $quoteId)->update(['sum_amount' => $sumAmount,
            'sum_tax' => $sumTax,
            'sum_tax_amount' => $sumTaxAmount,
            'sum_qty' => $sumQty,
        ]);
        $bossUrl = env('BOSS_URL');
        if ($base['bill_status'] === 'C') {
            (new SubRepo)->updateData($base['inquiry_id']);
            $supplierName = \App\Common\Models\Supplier::where('id', $supplierId)->value('name');
            $messageId = Message::insertGetId([
                        'receiver_type' => 'PURCHASER',
                        'content_url' => $bossUrl . '/front/#/inquiryRate/quoteDetails?id=' . $quoteId,
                        'sender_id' => $supplierId,
                        'message_type' => 'SYSTEM',
                        'message_title' => '报价单通知',
                        'message' => '【' . $supplierName . '】已对【' . $inquiry->inquiry_title . '】进行报价。',
                        'created_at' => date('Y-m-d H:i:s'),
            ]);
            $data = [
                'message_id' => $messageId,
                'receiver_id' => $inquiry->person_id,
                'supplier_id' => $supplierId,
                'org_id' => $inquiry->org_id,
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
            MessageReceiver::insert($data);
            $email = User::where('user_id', $inquiry->person_id)->value('email');
            !empty($email) ? (new SendLogRepo())->supplierQuote($email, $supplierName, $inquiry->inquiry_title, $base['inquiry_id']) : null;
        }
        return $flag;
    }

    /**
     * @param int $inquiryId
     * @param Request $request
     * 
     * @return array
     */
    public function add(Request $request) {
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
        $inquiry = Inquiry::where('id', $base['inquiry_id'])->first();
        if (empty($inquiry)) {
            check(false, '询价单不存在');
        }
        if ($inquiry->bill_status === 'A') {
            check(false, '询单已被撤销');
        }
        check($inquiry->bid_status !== 'E', '已终止的询价但不能报价');
        if (!empty($inquiry->end_date) && $inquiry->end_date < date('Y-m-d H:i:s')) {
            check(false, '已过报价有效期');
        }
        if ($inquiry->turns > 1) {
            $entryTurns = Supplier::where('inquiry_id', $base['inquiry_id'])
                    ->where('supplier_id', $supplierId)
                    ->orderBy('entry_turns', 'DESC')
                    ->value('entry_turns');
            check($inquiry->turns === $entryTurns, '您不是多轮报价邀请供应商,不能报价');
        }

        $quote = Quote::where('inquiry_id', $base['inquiry_id'])
                ->selectRaw('bill_status,supplier_id,id')
                ->where('turns', $inquiry['turns'])
                ->where('deleted_flag', 'N')
                ->where('bill_status', 'A')
                ->where('supplier_id', $supplierId)
                ->first();
        if ($quote) {
            $quoteId = $quote->id;
        }
        $entryCount = Quote::where('inquiry_id', $base['inquiry_id'])
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->where('bill_status', 'C')
                ->count();
        $contact = (new SupplierContactRepo)->getDefaultContact($supplierId);
        $quoteData = [
            'inquiry_id' => $base['inquiry_id'],
            'inquiry_title' => !empty($inquiry['title']) ? $inquiry['title'] : null,
            'bill_no' => !empty($base['bill_no']) ? $base['bill_no'] : $this->getQuoteNo(),
            'inquiry_no' => !empty($inquiry['bill_no']) ? $inquiry['bill_no'] : null,
            'bill_date' => date('Y-m-d H:i:s'),
            'biz_type' => $inquiry['biz_type'],
            'end_date' => $inquiry['end_date'],
            'req_org_id' => $inquiry['req_org_id'],
            'org_id' => $inquiry['org_id'],
            'rcv_org_id' => $inquiry['rcv_org_id'],
            'settle_org_id' => $inquiry['settle_org_id'],
            'pay_org_id' => $inquiry['pay_org_id'],
            'person_id' => $inquiry['person_id'],
            'date_from' => $inquiry['date_from'],
            'date_to' => $inquiry['date_to'],
            'settle_type_id' => !empty($base['settle_type_id']) ? $base['settle_type_id'] : null,
            'curr_id' => $inquiry['curr_id'],
            'loc_curr_id' => $inquiry['loc_curr_id'],
            'exch_type_id' => $inquiry['exch_type_id'],
            'exch_rate' => $inquiry['exch_rate'],
            'tax_type' => $inquiry['tax_type'],
            'cfm_status' => $inquiry['cfm_status'],
            'biz_partner_id' => $inquiry['biz_partner_id'],
            'deli_date' => $inquiry['deli_date'],
            'deli_addr' => $inquiry['deli_addr'],
            'bill_type_id' => $inquiry['bill_type_id'],
            'inv_type' => $inquiry['inv_type'],
            'total_inquiry' => $inquiry['total_inquiry'],
            'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
            'contact_phone' => !empty($contact['phone']) ? $contact['phone'] : '',
            'contact_email' => !empty($contact['email']) ? $contact['email'] : '',
            'turns' => $inquiry['turns'],
            'turns_count' => $entryCount + 1,
            'sup_curr_type' => $inquiry['sup_curr_type'],
            'rate_date' => $inquiry['rate_date'],
            'remark' => $inquiry['remark'],
            'inquiry_title' => $inquiry['inquiry_title'],
            'payment_terms' => !empty($base['payment_terms']) ? $base['payment_terms'] : null,
            'other_pay_terms_info' => !empty($base['other_pay_terms_info']) ? $base['other_pay_terms_info'] : null,
            'settlement_method' => !empty($base['settlement_method']) ? $base['settlement_method'] : null,
            'settlement_cur' => $inquiry['settlement_cur'],
            'tax_cal_type' => $inquiry['tax_cal_type'],
            'exchange_rate_date' => $inquiry['exchange_rate_date'],
            'text_field' => $inquiry['text_field'],
            'warranty_period' => $inquiry['warranty_period'],
            'business_type_id' => $inquiry['business_type_id'],
            'source' => $inquiry['source'],
            'delivery_date' => !empty($base['delivery_date']) ? $base['delivery_date'] : null,
            'warranty_period' => !empty($base['warranty_period']) ? $base['warranty_period'] : null,
            'bill_status' => !empty($base['bill_status']) ? $base['bill_status'] : 'A',
            'biz_status' => !empty($base['bill_status']) && $base['bill_status'] === 'C' ? 'A' : '',
            'supplier_id' => $supplierId,
            'source' => !empty($base['source']) ? $base['source'] : null,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $admin->user_id,
        ];
        if (!empty($quoteId)) {
            unset($quoteData['bill_no'], $quoteData['bill_date']);
            Quote::where('id', $quoteId)->update($quoteData);
        } else {
            $quoteId = Quote::insertGetId($quoteData);
        }
        $subId = QuoteSub::where('quote_id', $quoteId)->value('id');
        !empty($subId) ? QuoteSub::where('quote_id', $quoteId)
                                ->update([
                                    'quote_id' => $quoteId,
                                    'modifier_id' => $admin->user_id,
                                    'modify_time' => date('Y-m-d H:i:s'),
                                    'updated_by' => $admin->user_id,
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ]) : QuoteSub::insert([
                            'quote_id' => $quoteId,
                            'created_by' => $admin->user_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'creator_id' => $admin->user_id,
                            'create_time' => date('Y-m-d H:i:s'),
        ]);

        if (!empty($base['bill_status']) && $base['bill_status'] === 'C') {
            $contact = (new SupplierContactRepo)->getDefaultContact($supplierId);
            Supplier::upsert(['inquiry_id' => $base['inquiry_id'],
                'supplier_id' => $supplierId,
                'quote_date' => date('Y-m-d H:i:s'),
                'quoter_id' => $admin->user_id,
                'quote_id' => $quoteId,
                'entry_status' => 'A',
                'supplier_biz_status' => 'B',
                'entry_turns' => $inquiry['turns'],
                'entry_count' => $entryCount + 1,
                'dead_line' => $inquiry['end_date'],
                'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
                'contact_phone' => !empty($contact['phone']) ? $contact['phone'] : '',
                'contact_email' => !empty($contact['email']) ? $contact['email'] : '',
                'created_by' => $admin->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_by' => $admin->user_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_flag' => 'N',
                    ], ['supplier_id', 'inquiry_id', 'entry_turns'], ['quote_date',
                'quote_date',
                'quoter_id',
                'quote_id',
                'entry_status',
                'supplier_biz_status',
                'entry_turns',
                'entry_count',
                'dead_line',
                'contact_name',
                'contact_phone',
                'contact_email',
            ]);
        }
        (new AttachRepo)->updateData($quoteId, $request);
////        (new SupplierRepo)->updateData($inquiryId, $request);
        list($sumAmount, $sumTax, $sumTaxAmount, $sumQty) = (new QuoteEntryRepo())->updateData($base['inquiry_id'], $quoteId, $request);
        Quote::where('id', $quoteId)->update(['sum_amount' => $sumAmount,
            'sum_tax' => $sumTax,
            'sum_tax_amount' => $sumTaxAmount,
            'sum_qty' => $sumQty,
        ]);
        $bossUrl = env('BOSS_URL');
        if ($base['bill_status'] === 'C') {
            (new SubRepo)->updateData($base['inquiry_id']);
            $supplierName = \App\Common\Models\Supplier::where('id', $supplierId)->value('name');
            $messageId = Message::insertGetId([
                        'receiver_type' => 'PURCHASER',
                        'content_url' => $bossUrl . '/front/#/inquiryRate/quoteDetails?id=' . $quoteId,
                        'sender_id' => $supplierId,
                        'message_type' => 'SYSTEM',
                        'message_title' => '报价单通知',
                        'message' => '【' . $supplierName . '】已对【' . $inquiry->inquiry_title . '】进行报价。',
                        'created_at' => date('Y-m-d H:i:s'),
            ]);
            $data = [
                'message_id' => $messageId,
                'receiver_id' => $inquiry->person_id,
                'supplier_id' => $supplierId,
                'org_id' => $inquiry->org_id,
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
            MessageReceiver::insert($data);
            $email = User::where('user_id', $inquiry->person_id)->value('email');
            !empty($email) ? (new SendLogRepo())->supplierQuote($email, $supplierName, $inquiry->inquiry_title, $base['inquiry_id']) : null;
        }
        return (string) $quoteId;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
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
                ->selectRaw('id,status,deleted_flag,enable')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        check($supplier->status === 'APPROVED', '供应商没有准入');
        check($supplier->enable == '1', '供应商已被禁用');
        $ids = $request->ids;
        return Quote::whereIn('id', $ids)
                        ->where('supplier_id', $supplierId)
                        ->where('bill_status', 'A')
                        ->delete();
    }

    /**
     * 导出
     * @param $request
     * @return array
     */
    public function export(Request $request) {
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
        $filed = 'id,bill_no,org_id,'
                . 'inquiry_title,bill_date,sum_tax_amount,biz_status,'
                . 'bill_status,curr_id,supplier_id,loc_curr_id,inquiry_no';
        $query = $this->model
                ->selectRaw($filed);
        $query->where('supplier_id', $supplierId);
        if ($request->type === 'ALL') {
            $query->where('deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('deleted_flag', 'N')
                    ->whereIn('id', $request->ids);
        } else {
            $this->getWhere($query, $request);
        }
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            $item['biz_status_name'] = $this->getBizStatusText($item['biz_status']);
        }
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrencys($data, 'curr_id', ['curr_number' => 'number', 'curr_sign' => 'sign']);
        (new CurrencyRepo)->setCurrencys($data, 'loc_curr_id', ['loc_curr_number' => 'number', 'loc_curr_sign' => 'sign']);
        (new SupplierBaseRepo)->setSuppliers($data, 'supplier_id', 'supplier_name');
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        $headName = $this->getHeadName();
        $xlsName = '报价单_' . date("YmdHis", time()) . uniqid(); //文件名称
        return $this->downloadExcel($xlsName, $data, $headName);
    }

    private $styleArray = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ],
        'font' => [
            'name' => 'Arial',
            'bold' => false,
            'italic' => false,
            'size' => 9,
            'underline' => Font::UNDERLINE_NONE,
            'strikethrough' => false,
            'color' => [
                'rgb' => '000000'
            ]
        ],
        'numberFormat' => ['formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT],
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '00000000'],
            ],
        ],
    ];

    public function setExcelRow($sheet, $col, $row, $value, $width) {
        $sheet->setCellValue($col . $row, $value);
        $sheet->getStyle($col . $row)->applyFromArray($this->styleArray);
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    /**
     * 导出
     * @param type $request Description
     * @param $name
     * @param array $data
     * @param array $head
     * @return array
     */
    public function downloadExcel($name, $data = [], $head = []) {
        $count = count($head);  //计算表头数量
        $spreadsheet = Excel::newSpreadsheet();
        $styleArray = $this->styleArray;
        $sheet = $spreadsheet->getSpreadsheet()->getActiveSheet();
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->setCellValue('A1', '报价单');
        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始
            $this->setExcelRow($sheet, strtoupper(chr($i)), 2, $head[$i - 65], 20);
        }
        $row = 3;
        foreach ($data as $item) {
            //数字转字母从65开始：
            $this->setExcelRow($sheet, 'A', $row, ' ' . $item['bill_no'], 17);
            $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, $item['inquiry_no'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['inquiry_title'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['bill_date'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, '￥' . number_format($item['sum_tax_amount'], 2, '.', ','), 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'G', $row, $item['biz_status_name'], 24);
            $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
            $row++;
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:G2')
                ->applyFromArray($styleArray);
        $realtive = "/download/" . date("Ymd") . '/';
        $filename = $name . '.xlsx';
        $filedir = base_path() . '/public' . $realtive;
        @mkdir($filedir, 0777, true);
        $filepath = $filedir . $filename;
        $spreadsheet->save($filepath);
        $url = env('APP_URL') . $realtive . $filename;
        return ['file_url' => $url, 'attach_name' => $filename];
    }

    /**
     * 获取headName
     * @param $data
     * @return array
     */
    public function getHeadName() {
        return [
            '报价单号',
            '询价方',
            '询价单号',
            '询价标题',
            '报价日期',
            '价税合计',
            '项目状态'
        ];
    }

    public function getBillStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '待报价';
            case 'B':
                return '报价中';
            case 'C':
                return '已报价';
            case 'D':
                return '已关闭';
            case 'Z':
                return '已作废';
        }
    }

    public function getStatusText($status, $bizStatus) {
        switch (strtoupper($status)) {
            case 'A':
                return ['待报价', 'A'];
            case 'B':
                return ['报价中', 'B'];
            case 'C':
                switch (strtoupper($bizStatus)) {
                    case 'A':
                        return ['已报价', 'AA'];
                    case 'B':
                        return ['已开标', 'AB'];
                    case 'C':
                        return ['已采纳', 'AC'];
                    case 'D':
                        return ['部分采纳', 'AD'];
                    case 'E':
                        return ['未采纳', 'AE'];
                }
            case 'D':
                return ['已关闭', 'D'];
            case 'Z':
                return ['已作废', 'Z'];
        }
    }

    public function getBizStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '已报价';
            case 'B':
                return '已开标';
            case 'C':
                return '已采纳';
            case 'D':
                return '部分采纳';
            case 'E':
                return '未采纳';
        }
    }

    public function getBizStatusList() {
        return [
            'A' => '已报价',
            'B' => '已开标',
            'C' => '已采纳',
            'D' => '部分采纳',
            'E' => '未采纳',
        ];
    }

    public function getBillStatusList() {
        return [
            'A' => '待报价',
            'B' => '报价中',
            'C' => '已报价',
            'D' => '已关闭',
            'Z' => '已作废',
        ];
    }

    public function getStatusList() {
        return [
            'A' => '待报价',
            'B' => '报价中',
            'CA' => '已报价',
            'CB' => '已开标',
            'CC' => '已采纳',
            'CD' => '部分采纳',
            'CE' => '未采纳',
            'D' => '已关闭',
            'Z' => '已作废',
        ];
    }

    public function setQuoteIds(&$list, $supplierId) {
        if (empty($list)) {
            return;
        }
        $inquiryIds = [];
        foreach ($list as &$item) {
            $item['quote_id'] = null;
            $item['quote_turns'] = null;
            $inquiryIds[] = $item['id'];
        }
        $quoteTable = (new Quote)->getTable();
        $quoteQuery = Quote::from($quoteTable . ' as qs')
                ->selectRaw('qs.inquiry_id,max(qs.turns) as max_turns')
                ->where('qs.deleted_flag', 'N')
                ->where('qs.supplier_id', $supplierId)
                ->orderBy('qs.turns', 'DESC')
                ->groupBy('qs.inquiry_id');
        $quoteObj = $this->model
                ->selectRaw('q.inquiry_id,q.id,q.turns')
                ->from($this->model->getTable() . ' as q')
                ->leftJoinSub($quoteQuery, 'sq', function ($join) {
                    $join->on('q.turns', '=', 'sq.max_turns')
                    ->on('q.inquiry_id', '=', 'sq.inquiry_id');
                })
                ->where('q.supplier_id', $supplierId)
                ->get();
        if (empty($quoteObj)) {
            return;
        }
        $quoteList = $quoteObj->toArray();
        $quoteArr = [];
        foreach ($quoteList as $quote) {
            $quoteArr[$quote['inquiry_id']] = $quote;
        }
        foreach ($list as &$item) {
            if (!empty($quoteArr[$item['id']])) {
                $item['quote_id'] = $quoteArr[$item['id']]['id'];
                $item['quote_turns'] = $quoteArr[$item['id']]['turns'];
            }
        }
    }

}
