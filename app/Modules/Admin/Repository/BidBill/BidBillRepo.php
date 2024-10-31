<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    BidBill\BidBill,
    BidBill\BidBillEntry,
    BidBill\BidBillPay,
    BidBill\BidBillQuote,
    BidBill\BidBillSupplier,
    BidBill\Sub,
    Message,
    MessageReceiver,
    NoticeSub,
    Purchaser,
    User,
    UserSupplier
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    BidBill\BidBillPayRepo,
    BidBill\EntryRepo,
    BidBill\SupplierRepo AS BidBillSupplierRepo,
    CurrencyRepo,
    NoticeManageRepo,
    PaycondRepo,
    SettleMentTypeRepo,
    SupplierBaseRepo,
    SupplierContactRepo,
    UnitRepo,
    UserRepo,
    OrgRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB
};

class BidBillRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'name',
        'bid_status',
        'bill_status',
        'org_id',
        'bill_date',
        'enroll_date',
        'open_date',
        'result_date',
        'created_at',
    ];

    public function __construct() {
        $this->model = new BidBill();
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
    public function getList(Request $request, $filed = 'id,bill_no,deposit_flag,'
    . 'name,bill_status,org_id,bill_date,bill_status,bid_status,bid_number,check_type,'
    . 'enroll_date,open_date,result_date,sum_tax_amount,created_by,created_at,reduce_type,quotation_trend,reducepct') {
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
            $item['bid_status_name'] = $this->getBidStatusText($item['bid_status']);
            $item['reduce_type_name'] = $this->getReduceTypeText($item['reduce_type']);
            $item['quotation_trend_name'] = $this->getQuotationTrendText($item['quotation_trend']);
        }
        (new OrgRepo)->setOrgs($data, 'org_id', 'org_name');
        (new SubRepo)->setSubs($data, 'id');
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new BidBillSupplierRepo)->setQuoteNums($data);
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function todoTotal() {
        $query = BidBill::where('bill_status', 'A');
        return $query->count();
    }

    public function startTotal() {
        $query = BidBill::where('bill_status', 'C')
                ->where(function ($qs) {
            $qs->where(function ($q) {
                $q->where('deposit_flag', 'Y')
                ->where('bid_status', 'K');
            })
            ->orWhere(function ($q) {
                $q->where('check_type', '1')
                ->where('deposit_flag', 'N')
                ->where('bid_status', 'B');
            })
            ->orWhere(function ($q) {
                $q->where('check_type', '3')
                ->where('deposit_flag', 'N')
                ->where('bid_status', 'I');
            });
        });
        return $query->count();
    }

    public function decisionTotal() {
        $query = BidBill::where('bill_status', 'C')
                ->where('bid_status', 'D');
        return $query->count();
    }

    public function getTotal(Request $request) {

        $query = BidBill::whereRaw('1=1');
        $this->getWhere($query, $request);
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
        $base = $object->toArray();
        $base['max_amount'] = !empty($base['max_amount']) ? number_format($base['max_amount'], 2, '.', '') : null;
        $base['min_amount'] = !empty($base['min_amount']) ? number_format($base['min_amount'], 2, '.', '') : null;
        $base['cash_deposit'] = !empty($base['cash_deposit']) ? number_format($base['cash_deposit'], 2, '.', '') : null;
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
        (new UserRepo)->setUser($base, 'person_id', 'person_name');
        (new UserRepo)->setUser($base, 'created_by', 'created_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settle_type_id', 'settle_type_name');
        (new PaycondRepo)->setPaycond($base, 'pay_cond_id', ['pay_cond_name' => 'name']);
        (new CurrencyRepo)->setCurrency($base, 'curr_id', 'curr_name');
        (new OrgRepo)->setOrg($base, 'org_id', 'org_name');
        $base['category_name'] = $this->getCategoryText($base['required_category']);
        (new BidBillSupplierRepo)->setQuoteNum($base);
        $data['base'] = $base;
        $base['inv_type_name'] = $this->getInvtypeText($base['inv_type']);
        $data['attachs'] = (new AttachRepo)->getList($id);
        $data['entrys'] = (new EntryRepo)->getList($id);
        $data['quotes'] = (new QuoteRepo)->getList($id);
        (new QuoteAttachRepo)->setQuoteAttachs($data['quotes']);
        $data['pays'] = (new BidBillPayRepo)->getList($id);
        $data['suppliers'] = (new SupplierRepo)->getList($id);
        $data['sub'] = (new SubRepo)->info($id);
        return $data;
    }

    /**
     * @param int $bidBillID
     * @param Request $request
     * 
     * @return array
     */
    public function edited($bidBillID, Request $request) {
        $admin = Auth::guard('admin')->user();
        $base = $request->base;
        $bidBillData = [
            'add_remark' => !empty($base['add_remark']) ? $base['add_remark'] : null,
            'auto_confirm' => !empty($base['auto_confirm']) ? $base['auto_confirm'] : null,
            'bid_count' => !empty($base['bid_count']) ? $base['bid_count'] : null,
            'bid_number' => !empty($base['bid_number']) ? $base['bid_number'] : null,
            'bid_status' => null,
            'bid_time' => !empty($base['bid_time']) ? $base['bid_time'] : null,
            'bill_date' => date('Y-m-d H:i:s'),
            'bill_status' => !empty($base['bill_status']) ? $base['bill_status'] : 'A',
            'bill_type_id' => !empty($base['bill_type_id']) ? $base['bill_type_id'] : null,
            'biz_addr' => !empty($base['biz_addr']) ? $base['biz_addr'] : null,
            'biz_model' => !empty($base['biz_model']) ? $base['biz_model'] : null,
            'biz_partner_id' => !empty($base['biz_partner_id']) ? $base['biz_partner_id'] : null,
            'business_type_id' => !empty($base['business_type_id']) ? $base['business_type_id'] : null,
            'cash_deposit' => !empty($base['cash_deposit']) ? $base['cash_deposit'] : null,
            'certificate' => !empty($base['certificate']) ? $base['certificate'] : null,
            'cfm_status' => null,
            'check_type' => !empty($base['check_type']) ? $base['check_type'] : '3',
            'combo_field' => !empty($base['combo_field']) ? $base['combo_field'] : null,
            'curr_id' => !empty($base['curr_id']) ? $base['curr_id'] : null,
            'current_round' => !empty($base['current_round']) ? $base['current_round'] : null,
            'date_from' => !empty($base['date_from']) ? $base['date_from'] : null,
            'date_to' => !empty($base['date_to']) ? $base['date_to'] : null,
            'decision_info' => !empty($base['decision_info']) ? $base['decision_info'] : null,
            'delay_time' => !empty($base['delay_time']) ? $base['delay_time'] : null,
            'deli_addr' => !empty($base['deli_addr']) ? $base['deli_addr'] : null,
            'deli_date' => !empty($base['deli_date']) ? $base['deli_date'] : null,
            'deposit_flag' => !empty($base['deposit_flag']) ? $base['deposit_flag'] : 'N',
            'enroll_date' => !empty($base['enroll_date']) ? $base['enroll_date'] : null,
            'ex_price_explain' => !empty($base['ex_price_explain']) ? $base['ex_price_explain'] : null,
            'ex_price_explain_tag' => !empty($base['ex_price_explain_tag']) ? $base['ex_price_explain_tag'] : null,
            'final_price' => !empty($base['final_price']) ? $base['final_price'] : null,
            'interval_duration' => !empty($base['interval_duration']) ? $base['interval_duration'] : null,
            'inv_type' => !empty($base['inv_type']) ? $base['inv_type'] : null,
            'is_filter' => !empty($base['is_filter']) ? $base['is_filter'] : null,
            'is_free_quote' => !empty($base['is_free_quote']) ? $base['is_free_quote'] : null,
            'is_msg' => !empty($base['is_msg']) ? $base['is_msg'] : null,
            'last_time' => !empty($base['last_time']) ? $base['last_time'] : null,
            'max_amount' => !empty($base['max_amount']) ? $base['max_amount'] : null,
            'min_amount' => !empty($base['min_amount']) ? $base['min_amount'] : null,
            'multiple_rounds' => !empty($base['multiple_rounds']) ? $base['multiple_rounds'] : null,
            'name' => !empty($base['name']) ? $base['name'] : null,
            'open1' => !empty($base['open1']) ? $base['open1'] : null,
            'open2' => !empty($base['open2']) ? $base['open2'] : null,
            'open3' => !empty($base['open3']) ? $base['open3'] : null,
            'open_date' => !empty($base['open_date']) ? $base['open_date'] : null,
            'org_id' => !empty($base['org_id']) ? $base['org_id'] : null,
            'pay_cond_id' => !empty($base['pay_cond_id']) ? $base['pay_cond_id'] : null,
            'person_id' => !empty($base['person_id']) ? $base['person_id'] : null,
            'phone' => !empty($base['phone']) ? $base['phone'] : null,
            'promotion_ratio' => !empty($base['promotion_ratio']) ? $base['promotion_ratio'] : null,
            'publisher' => !empty($base['publisher']) ? $base['publisher'] : null,
            'pur_category' => !empty($base['pur_category']) ? $base['pur_category'] : null,
            'pur_officer' => !empty($base['pur_officer']) ? $base['pur_officer'] : null,
            'quotation_trend' => !empty($base['quotation_trend']) ? $base['quotation_trend'] : null,
            'quote_mode' => 1,
            'reduce_type' => !empty($base['reduce_type']) ? $base['reduce_type'] : null,
            'reducepct' => !empty($base['reducepct']) ? $base['reducepct'] : null,
            'regcapital' => !empty($base['regcapital']) ? $base['regcapital'] : null,
            'remark' => !empty($base['remark']) ? $base['remark'] : null,
            'required_category' => !empty($base['required_category']) ? $base['required_category'] : null,
            'required_level' => !empty($base['required_level']) ? $base['required_level'] : null,
            'result_date' => !empty($base['result_date']) ? $base['result_date'] : null,
            'settle_type_id' => !empty($base['settle_type_id']) ? $base['settle_type_id'] : null,
            'sum_amount' => !empty($base['sum_amount']) ? $base['sum_amount'] : null,
            'sum_qty' => !empty($base['sum_qty']) ? $base['sum_qty'] : null,
            'sum_tax' => !empty($base['sum_tax']) ? $base['sum_tax'] : null,
            'sum_tax_amount' => !empty($base['sum_tax_amount']) ? $base['sum_tax_amount'] : null,
            'supplier_list' => !empty($base['supplier_list']) ? $base['supplier_list'] : null,
            'tax_type' => !empty($base['tax_type']) ? $base['tax_type'] : null,
            'total_rounds' => !empty($base['total_rounds']) ? $base['total_rounds'] : null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id,
            'biz_type' => !empty($base['biz_type']) ? $base['biz_type'] : null,
        ];

        if (!empty($base['bill_status']) && in_array($base['bill_status'], ['B', 'C'])) {
            $bidBillData['bill_status'] = 'C';
            $bidBillData['bid_status'] = 'A';
            $sub['auditor_by'] = date('Y-m-d H:i:s');
            $sub['auditor_by'] = $admin->user_id;
        }
        $flag = BidBill::where('id', $bidBillID)->update($bidBillData);
        (new AttachRepo)->updateData($bidBillID, $request);
        (new SupplierRepo)->updateData($bidBillID, $request);
        $taxCalType = $bidBillData['tax_type'];
        list($sumAmount, $sumTax, $sumTaxAmount, $sumQty) = (new EntryRepo())->updateData($bidBillID, $request, $taxCalType);
        BidBill::where('id', $bidBillID)->update(['sum_amount' => $sumAmount,
            'sum_tax' => $sumTax,
            'sum_tax_amount' => $sumTaxAmount,
            'sum_qty' => $sumQty,
        ]);
        (new SubRepo())->updateData($bidBillID, $request);
        if ($bidBillData['bill_status'] !== 'C') {
            return $flag;
        }
        $bidBillData['id'] = $bidBillID;
        $this->sends($bidBillData, $bidBillID);
        $this->notice($bidBillID);
        return $flag;
    }

    /**
     * @param int $bidBillId
     * @param Request $request
     * 
     * @return array
     */
    public function add(Request $request) {
        $base = $request->base;
        $admin = Auth::guard('admin')->user();
        $bidBillData = [
            'bill_no' => !empty($base['bill_no']) ? $base['bill_no'] : $this->getBidBillNo(),
            'bill_date' => date('Y-m-d H:i:s'),
            'biz_type' => !empty($base['biz_type']) ? $base['biz_type'] : null,
            'check_type' => !empty($base['check_type']) ? $base['check_type'] : null,
            'deposit_flag' => !empty($base['deposit_flag']) ? $base['deposit_flag'] : 'N',
            'org_id' => !empty($base['org_id']) ? $base['org_id'] : null,
            'person_id' => !empty($base['person_id']) ? $base['person_id'] : null,
            'deli_date' => !empty($base['deli_date']) ? $base['deli_date'] : null,
            'deli_addr' => !empty($base['deli_addr']) ? $base['deli_addr'] : null,
            'phone' => !empty($base['phone']) ? $base['phone'] : null,
            'pay_cond_id' => !empty($base['pay_cond_id']) ? $base['pay_cond_id'] : null,
            'settle_type_id' => !empty($base['settle_type_id']) ? $base['settle_type_id'] : null,
            'curr_id' => !empty($base['curr_id']) ? $base['curr_id'] : null,
            'tax_type' => !empty($base['tax_type']) ? $base['tax_type'] : null,
            'inv_type' => !empty($base['inv_type']) ? $base['inv_type'] : null,
            'sum_amount' => !empty($base['sum_amount']) ? $base['sum_amount'] : null,
            'date_from' => !empty($base['date_from']) ? $base['date_from'] : null,
            'date_to' => !empty($base['date_to']) ? $base['date_to'] : null,
            'sum_tax' => !empty($base['sum_tax']) ? $base['sum_tax'] : null,
            'sum_tax_amount' => !empty($base['sum_tax_amount']) ? $base['sum_tax_amount'] : null,
            'sum_qty' => !empty($base['sum_qty']) ? $base['sum_qty'] : null,
            'cash_deposit' => !empty($base['cash_deposit']) ? $base['cash_deposit'] : null,
            'enroll_date' => !empty($base['enroll_date']) ? $base['enroll_date'] : null,
            'open_date' => !empty($base['open_date']) ? $base['open_date'] : null,
            'result_date' => !empty($base['result_date']) ? $base['result_date'] : null,
            'bid_time' => !empty($base['bid_time']) ? $base['bid_time'] : null,
            'last_time' => !empty($base['last_time']) ? $base['last_time'] : null,
            'delay_time' => !empty($base['delay_time']) ? $base['delay_time'] : null,
            'bid_count' => !empty($base['bid_count']) ? $base['bid_count'] : null,
            'max_amount' => !empty($base['max_amount']) ? $base['max_amount'] : null,
            'min_amount' => !empty($base['min_amount']) ? $base['min_amount'] : null,
            'reducepct' => !empty($base['reducepct']) ? $base['reducepct'] : null,
            'open1' => !empty($base['open1']) ? $base['open1'] : null,
            'open2' => !empty($base['open2']) ? $base['open2'] : null,
            'open3' => !empty($base['open3']) ? $base['open3'] : null,
            'is_free_quote' => !empty($base['is_free_quote']) ? $base['is_free_quote'] : null,
            'bill_status' => !empty($base['bill_status']) ? $base['bill_status'] : 'A',
            'cfm_status' => null,
            'bid_status' => null,
            'biz_partner_id' => !empty($base['biz_partner_id']) ? $base['biz_partner_id'] : null,
            'biz_model' => !empty($base['biz_model']) ? $base['biz_model'] : null,
            'certificate' => !empty($base['certificate']) ? $base['certificate'] : null,
            'biz_addr' => !empty($base['biz_addr']) ? $base['biz_addr'] : null,
            'regcapital' => !empty($base['regcapital']) ? $base['regcapital'] : null,
            'bill_type_id' => !empty($base['bill_type_id']) ? $base['bill_type_id'] : null,
            'reduce_type' => !empty($base['reduce_type']) ? $base['reduce_type'] : null,
            'bid_number' => !empty($base['bid_number']) ? $base['bid_number'] : null,
            'auto_confirm' => !empty($base['auto_confirm']) ? $base['auto_confirm'] : null,
            'publisher' => !empty($base['publisher']) ? $base['publisher'] : null,
            'name' => !empty($base['name']) ? $base['name'] : null,
            'remark' => !empty($base['remark']) ? $base['remark'] : null,
            'quote_mode' => 1,
            'add_remark' => !empty($base['add_remark']) ? $base['add_remark'] : null,
            'multiple_rounds' => !empty($base['multiple_rounds']) ? $base['multiple_rounds'] : null,
            'total_rounds' => !empty($base['total_rounds']) ? $base['total_rounds'] : null,
            'interval_duration' => !empty($base['interval_duration']) ? $base['interval_duration'] : null,
            'promotion_ratio' => !empty($base['promotion_ratio']) ? $base['promotion_ratio'] : null,
            'decision_info' => !empty($base['decision_info']) ? $base['decision_info'] : null,
            'current_round' => !empty($base['current_round']) ? $base['current_round'] : null,
            'final_price' => !empty($base['final_price']) ? $base['final_price'] : null,
            'supplier_list' => !empty($base['supplier_list']) ? $base['supplier_list'] : null,
            'ex_price_explain' => !empty($base['ex_price_explain']) ? $base['ex_price_explain'] : null,
            'ex_price_explain_tag' => !empty($base['ex_price_explain_tag']) ? $base['ex_price_explain_tag'] : null,
            'combo_field' => !empty($base['combo_field']) ? $base['combo_field'] : null,
            'is_filter' => !empty($base['is_filter']) ? $base['is_filter'] : null,
            'quotation_trend' => !empty($base['quotation_trend']) ? $base['quotation_trend'] : null,
            'required_category' => !empty($base['required_category']) ? $base['required_category'] : null,
            'required_level' => !empty($base['required_level']) ? $base['required_level'] : null,
            'is_msg' => !empty($base['is_msg']) ? $base['is_msg'] : null,
            'pur_officer' => !empty($base['pur_officer']) ? $base['pur_officer'] : null,
            'pur_category' => !empty($base['pur_category']) ? $base['pur_category'] : null,
            'business_type_id' => !empty($base['business_type_id']) ? $base['business_type_id'] : null,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $admin->user_id,
        ];
        if (!empty($base['bill_status']) && in_array($base['bill_status'], ['B', 'C'])) {
            $bidBillData['bill_status'] = 'C';
            $bidBillData['bid_status'] = 'A';
            $sub['audit_date'] = date('Y-m-d H:i:s');
            $sub['auditor_id'] = $admin->user_id;
        }
        $bidBillId = BidBill::insertGetId($bidBillData);
        $sub['bid_bill_id'] = $bidBillId;
        (new AttachRepo)->updateData($bidBillId, $request);
        (new SupplierRepo)->updateData($bidBillId, $request);
        $taxCalType = $bidBillData['tax_type'];
        list($sumAmount, $sumTax, $sumTaxAmount, $sumQty) = (new EntryRepo())->updateData($bidBillId, $request, $taxCalType);
        BidBill::where('id', $bidBillId)->update(['sum_amount' => $sumAmount,
            'sum_tax' => $sumTax,
            'sum_tax_amount' => $sumTaxAmount,
            'sum_qty' => $sumQty,
        ]);
        (new SubRepo())->updateData($bidBillId, $request);
        if ($bidBillData['bill_status'] !== 'C') {
            return $bidBillId;
        }
        $this->notice($bidBillId);
        $bidBillData['id'] = $bidBillId;
        $this->sends($bidBillData, $bidBillId);
        return $bidBillId;
    }

    public function sends($bidBillData, $bidBillId) {
        if ($bidBillData['bill_status'] !== 'C') {
            return;
        }
        $data = $this->notice($bidBillId);
        $nrequest = (new Request);
        $nrequest->merge($data);
        (new NoticeManageRepo)->addData($nrequest);
        if ($bidBillData['biz_type'] == 1) {
            return true;
        }
        $supplierIds = BidBillSupplier::where('bid_bill_id', $bidBillId)
                ->where('deleted_flag', 'N')
                ->pluck('supplier_id');
        if (empty($supplierIds)) {
            return [];
        }
        $orgName = Purchaser::where('id', $bidBillData['org_id'])
                ->value('name');
        $this->sendMail($bidBillData, $supplierIds, $orgName);
        $this->sendMessage($bidBillData, $bidBillId, $supplierIds, $orgName);
    }

    public function sendMessage($bidBillData, $bidBillId, $supplierIds) {

        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $bossUrl = env('BOSS_URL');

        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/biddingManage/biddingDetail?id=' . $bidBillId,
                    'sender_id' => $bidBillData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '竞价报名通知',
                    'message' => '【' . $bidBillData['name'] . '】的竞价项目邀请您报名',
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
                'org_id' => $bidBillData['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            MessageReceiver::insert($dataList);
        }
    }

    public function sendMail($bidBillData, $supplierIds, $orgName) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'bidBillData' => $bidBillData,
            'supplierIds' => $supplierIds,
            'orgName' => $orgName
                ], 'BIDBILL'));
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        $admin = Auth::guard('admin')->user();
        $count = BidBill::whereIn('id', $request->ids)
                ->count();
        $countN = BidBill::whereIn('id', $request->ids)
                ->whereIn('bill_status', ['B', 'C'])
                ->count();
        $countP = BidBill::whereIn('id', $request->ids)
                ->whereIn('bill_status', ['A'])
                ->whereNot(function ($q)use ($admin) {
                    $q->where('person_id', $admin->user_id)
                    ->orWhere('created_by', $admin->user_id);
                })
                ->count();
        DB::beginTransaction();
        $flag = BidBill::whereIn('id', $ids)
                ->where('bill_status', 'A')
                ->where(function ($q)use ($admin) {
                    $q->where('person_id', $admin->user_id)
                    ->orWhere('created_by', $admin->user_id);
                })
                ->delete();
        $str = '';
        if (!empty($flag)) {
            $str .= '成功删除' . $flag . '条';
        }
        if (!empty($countN)) {
            $str .= (!empty($str) ? '，' : '') . '已审核不能删除的竞价' . $countN . '条';
        }
        if (!empty($countP)) {
            $str .= (!empty($str) ? '，' : '') . '不是询单创建人或采购员不能删除的竞价' . $countP . '条';
        }
        DB::commit();
        check($count === $flag, $str);
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
            $query->where(function ($q)use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->bid_status)) {
            $bidStatus = $request->bid_status;
            $bidStatusies = is_array($bidStatus) ? $bidStatus : explode(',', trim($bidStatus));
            $query->whereIn('bid_status', $bidStatusies);
        }

        if (!empty($request->person_name)) {
            $user = (new \App\Common\Models\User)->getTable();
            $personName = trim($request->person_name);
            $query->WhereRaw('EXISTS(SELECT u.user_id FROM ' . $user
                    . ' as u WHERE u.realname like \'%' . $personName . '%\''
                    . ' AND u.deleted_flag=\'N\' AND u.user_id=bid_bill.person_id)');
        }
        if (!empty($request->bill_status)) {
            $billStatus = $request->bill_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('bill_status', $billStatusies);
        }
        if (!empty($request->check_type)) {
            $query->where('check_type', trim($request->check_type));
        }
        if (!empty($request->name)) {
            $query->where('name', 'like', '%' . trim($request->name) . '%');
        }
        if (!empty($request->biz_type)) {
            $query->where('biz_type', trim($request->biz_type));
        }
        if (!empty($request->start_status) && $request->start_status === 'Y') {
            $query->where(function($qs) {
                $qs->where(function($q) {//报价截止 不需资审和缴纳保证金
                            $q->where('bid_status', 'I')
                            ->where('check_type', 3)
                            ->where('deposit_flag', 'N');
                        })
                        ->orWhere(function($q) {//已通过资审 不需缴纳保证金
                            $q->where('bid_status', 'B')
                            ->where('check_type', 1)
                            ->where('deposit_flag', 'N');
                        })
                        ->orWhere(function($q) {//已通过资审 不需缴纳保证金
                            $q->where('bid_status', 'K')
                            ->where('deposit_flag', 'Y');
                        });
            });
        }
        if (!empty($request->statusies)) {
            $query->whereIn('bid_status', $request->statusies);
        }
        /**
         * 创建时间
         */
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bill_date', $createAts);
        }
        /**
         * 报名截止
         */
        if (!empty($request->enrolltype)) {
            $enrollAts = $this->getTimeByType($request->enrolltype);
            $query->whereBetween('enroll_date', $enrollAts);
        } elseif (!empty($request->enroll_date)) {
            $enrollDate = $request->enroll_date;
            $enrollDates = is_array($enrollDate) ? $enrollDate : explode(',', $enrollDate);
            !empty($enrollDates[1]) ? $enrollDates[1] = date('Y-m-d 23:59:59', strtotime($enrollDates[1])) : $enrollDates[1] = date('Y-m-d H:i:s');
            $query->whereBetween('enroll_date', $enrollDates);
        }
        /**
         * 预计竞价开始时间
         */
        if (!empty($request->opentype)) {
            $openDates = $this->getTimeByType($request->opentype);
            $query->whereBetween('open_date', $openDates);
        } elseif (!empty($request->open_date)) {
            $openDate = $request->open_date;
            $openDates = is_array($openDate) ? $openDate : explode(',', $openDate);
            !empty($openDates[1]) ? $openDates[1] = date('Y-m-d 23:59:59', strtotime($openDates[1])) : $openDates[1] = date('Y-m-d H:i:s');
            $query->whereBetween('open_date', $openDates);
        }
        /**
         * 预计公布结果时间
         */
        if (!empty($request->resulttype)) {
            $resultAts = $this->getTimeByType($request->resulttype);
            $query->whereBetween('result_date', $resultAts);
        } elseif (!empty($request->result_date)) {
            $resultDate = $request->result_date;
            $resultDates = is_array($resultDate) ? $resultDate : explode(',', $resultDate);
            !empty($resultDates[1]) ? $resultDates[1] = date('Y-m-d 23:59:59', strtotime($resultDates[1])) : $resultDates[1] = date('Y-m-d H:i:s');
            $query->whereBetween('result_date', $resultDates);
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
                return '待收取保证金';
            case 'M':
                return '已终止';
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
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getBidBillNo($newNumber = null) {
        $prefix = 'JJ';
        $qurey = $this->model->selectRaw('*');
        $billNo = $newNumber ? $newNumber : $qurey
                        ->where('bill_no', 'like', $prefix . '%')
                        ->orderBy('bill_no', 'DESC')
                        ->value('bill_no');
        if (!empty($billNo)) {
            $date = substr($billNo, 2, 8);
            $serialSetp = substr($billNo, 10, 5);
            $step = intval($serialSetp);
            $step++;
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
     * 变更项目截止时间
     * @param Request $request
     */
    public function changeEnrollDate(Request $request) {
        $id = $request->id;
        $bidBill = BidBill::where('id', $id)->first();
        $admin = Auth::guard('admin')->user();
        if (empty($bidBill)) {
            check(false, '竞价单不存在');
        }

        $data = [
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id,
        ];
        if ($bidBill->bill_status !== 'C') {
            check(false, '竞价单不是已审核');
        }
        $time = date('Y-m-d H:i:s');
        if (!empty($request->enroll_date) && in_array($bidBill->bid_status, ['A', 'I'])) {
            check($request->enroll_date > $time, '报名截止时间必须大于当前时间');
            $data['enroll_date'] = $request->enroll_date;
        }
        if (!empty($data['enroll_date'])) {
            check($data['enroll_date'] > $time, '报名截止时间必须大于当前时间');
        }
        if (!empty($data['enroll_date'])) {
            $data['bid_status'] = 'A';
        }

        if (!empty($request->open_date) && in_array($bidBill->bid_status, ['A', 'B', 'I', 'K', 'L'])) {
            $data['open_date'] = $request->open_date;
        }
        if (!empty($data['enroll_date']) && !empty($data['open_date'])) {
            check($data['open_date'] > $data['enroll_date'], '预计竞价开始时间必须大于报名截止时间');
        } elseif (!empty($data['open_date']) && empty($data['enroll_date'])) {
            check($data['open_date'] > $bidBill->enroll_date, '预计竞价开始时间必须大于报名截止时间');
            check($data['open_date'] > $time, '预计竞价开始时间必须大于当前时间');
        }

        if (!empty($request->result_date)) {
            $data['result_date'] = $request->result_date;
        }

        if (!empty($data['result_date']) && !empty($data['open_date'])) {
            check($data['result_date'] > $data['open_date'], '预计公布结果时间必须大于预计竞价开始时间');
        } elseif (!empty($data['result_date']) && empty($data['open_date'])) {
            check($data['result_date'] > $bidBill->open_date, '预计公布结果时间必须大于预计竞价开始时间');
            check($data['result_date'] > $time, '预计公布结果时间必须必须大于当前时间');
        }
        $flag = BidBill::where('id', $id)
                ->update($data);
        BidBillSupplier::where('entry_status', 'WCY')->update([
            'entry_status' => 'T',
        ]);
        if (empty($data['enroll_date'])) {
            return $flag;
        }
        $ndata = $this->notice($id);
        $nrequest = (new Request);
        $nrequest->merge($ndata);
        $noticeId = NoticeSub::where('src_bill_id', $id)
                ->where('src_bill_type', 'sou_bidbill')
                ->value('notice_id');
        if (empty($noticeId)) {
            return $flag;
        }
        (new NoticeManageRepo)->edited($noticeId, $nrequest);
        return $flag;
    }

    /**
     * 资质审查
     * @param Request $request
     */
    public function check(int $id, Request $request) {
        $suppliers = $request->suppliers;
        if (empty($suppliers)) {
            check(false, '请选择资质审查的供应商');
        }
        $bidBill = BidBill::where('id', $id)
                        ->select('enroll_date', 'bill_status', 'biz_type', 'bid_number', 'bid_status', 'deposit_flag', 'cash_deposit', 'name', 'bill_no', 'org_id', 'id', 'person_id'
                        )->first();
        if (empty($bidBill)) {
            check(false, '竞价不存在');
        }
        if ($bidBill && $bidBill->enrollDate > date('Y-m-d H:i:s')) {
            check(false, '不是报名截止的竞价单不允许资审');
        }

        if ($bidBill->bid_status !== 'I') {
            check(false, '不是报名截止的竞价单不允许资审');
        }
        if ($bidBill->check_type === '3') {
            check(false, '资格免审，不需要审查');
        }
        $quoteCount = BidBillSupplier::where('bid_bill_id', $id)
                ->whereIn('entry_status', ['Y', 'A', 'B', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'WQR', 'M', 'O', 'P', 'Q', 'Y'])
                ->count();
        if (!empty($bidBill->bid_number) && $bidBill->bid_number > $quoteCount) {
            check(false, '不满足供应商报名条件，不需要审查');
        }

//不是报名截止的竞价单不允许资审
        $admin = Auth::guard('admin')->user();
        $payList = $dataList = [];
        $allowSupplierIds = [];
        $time = date('Y-m-d H:i:s');
        foreach ($suppliers as $supplier) {
            if (empty($supplier['entry_status']) || $supplier['entry_status'] === 'A') {
                continue;
            }
            $allowBid = $bidBill->deposit_flag === 'N' && $supplier['entry_status'] === 'B' ? '1' : '0';
            if ($allowBid == 1) {
                $supplier['entry_status'] = 'B';
                $allowSupplierIds[] = $supplier['supplier_id'];
            } elseif ($bidBill->deposit_flag === 'Y' && $supplier['entry_status'] === 'B') {
                $supplier['entry_status'] = 'L';
            }
            $dataList[] = [
                'audit_date' => $time,
                'bid_bill_id' => $id,
                'supplier_id' => $supplier['supplier_id'],
                'entry_status' => !empty($supplier['entry_status']) ? $supplier['entry_status'] : 'A',
                'audit_id' => $admin->user_id,
                'allow_bid' => $allowBid,
            ];
            if ($bidBill->deposit_flag != 'Y') {
                continue;
            }
            $contact = (new SupplierContactRepo)->getDefaultContact($supplier['supplier_id']);
            $payList[] = [
                'bill_no' => (new BidBillPayRepo)->getBidBillPayNo(),
                'bid_bill_id' => $id,
                'bid_bill_no' => $bidBill->bill_no,
                'bid_bill_name' => $bidBill->name,
                'org_id' => $bidBill->org_id,
                'supplier_id' => $supplier['supplier_id'],
                'sure_amount' => $bidBill->cash_deposit,
                'bill_status' => 'A',
                'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
                'contact_phone' => !empty($contact['phone']) ? $contact['phone'] : '',
                'contact_email' => !empty($contact['email']) ? $contact['email'] : '',
                'created_at' => $time
            ];
        }

        if (!empty($dataList)) {
            $flag = BidBillSupplier::upsert($dataList, ['bid_bill_id',
                        'supplier_id'], [
                        'audit_date',
                        'allow_bid',
                        'contact_name',
                        'contact_phone',
                        'contact_email',
                        'entry_status',
                        'audit_id']);
        }
        if (!empty($payList)) {
            BidBillPay::upsert($payList, ['bid_bill_id', 'supplier_id'], ['bill_status',
                'sure_amount',
                'org_id',
                'bid_bill_no',
                'bid_bill_name',
            ]);
        }
        $count = BidBillSupplier::where('bid_bill_id', $id)
                ->where('entry_status', 'A')
                ->count();
        if ($count > 0) {
            return $flag;
        }

        if ($bidBill->deposit_flag === 'Y') {
            Sub::where('bid_bill_id', $id)
                    ->update([
//                        'enroll_number' => count($allowSupplierIds),
                        'cfm_id' => $admin->user_id,
                        'cfm_at' => date('Y-m-d H:i:s'),
            ]);
            BidBillSupplier::where('bid_bill_id', $id)
                    ->where('entry_status', 'B')
                    ->update(['entry_status' => 'L']);
            $this->sendPayMail($bidBill->toArray(), $id);
        }
        $this->sendCheckNotMail($bidBill->toArray(), $id);
        return BidBill::where('id', $id)->update([
                    'bid_status' => 'B',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $admin->user_id
        ]);
    }

    public function sendPayMessage($bidBillData, $id) {
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/biddingManage/biddingDetail?id=' . $id,
                    'sender_id' => 1,
                    'message_type' => 'SYSTEM',
                    'message_title' => '竞价待缴费通知',
                    'message' => '【' . $bidBillData['name'] . '】已开启收取保证金，请及时缴费。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);

        $supplier = (new BidBillSupplier)->getTable();
        $user = (new User())->getTable();
        $userObj = User::from($user . ' as u')
                ->join($supplier . ' as us', function ($join) {
                    $join->on('u.user_id', '=', 'us.enroll_id');
                })
                ->whereIn('us.bid_bill_id', $id)
                ->selectRaw('u.user_id,us.supplier_id')
                ->groupBy('us.supplier_id')
                ->groupBy('us.user_id')
                ->get();
        if (empty($userObj)) {
            return;
        }

        $userList = $userObj->toArray();
        foreach ($userList as $user) {
            $dataList[] = [
                'message_id' => $messageId,
                'receiver_id' => $user['user_id'],
                'supplier_id' => $user['supplier_id'],
                'org_id' => $bidBillData['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            MessageReceiver::insert($dataList);
        }
    }

    public function sendCheckNotMessage($bidBillData, $id) {
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/biddingManage/biddingDetail?id=' . $bidBill['id'],
                    'sender_id' => 1,
                    'message_type' => 'SYSTEM',
                    'message_title' => '竞价资审未通过通知',
                    'message' => '很遗憾，【' . $bidBillData['name'] . '】资审未通过。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $supplier = (new BidBillSupplier)->getTable();
        $user = (new User())->getTable();
        $userObj = User::from($user . ' as u')
                ->join($supplier . ' as us', function ($join) {
                    $join->on('u.user_id', '=', 'us.enroll_id');
                })
                ->whereIn('us.bid_bill_id', $id)
                ->selectRaw('u.user_id,us.supplier_id')
                ->groupBy('us.supplier_id')
                ->groupBy('us.user_id')
                ->get();
        if (empty($userObj)) {
            return;
        }

        $userList = $userObj->toArray();
        foreach ($userList as $user) {
            $dataList[] = [
                'message_id' => $messageId,
                'receiver_id' => $user['user_id'],
                'supplier_id' => $user['supplier_id'],
                'org_id' => $bidBillData['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            MessageReceiver::insert($dataList);
        }
    }

    public function sendPayMail($bidBillData, $id) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'bidBillData' => $bidBillData,
            'id' => $id,
                ], 'BIDBILL_TOBEPAY'));
    }

    public function sendCheckNotMail($bidBillData, $id) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'bidBillData' => $bidBillData,
            'id' => $id,
                ], 'BIDBILL_CHECKNOT'));
    }

    /**
     * 竞价暂停
     * @param int $id
     */
    public function stop(int $id, Request $request) {
        $bidBill = BidBill::where('id', $id)
                ->select('enroll_date', 'bid_status', 'bid_time', 'open_date', 'last_time', 'delay_time')
                ->first();
        if ($bidBill->bid_status !== 'C') {
            check(false, '不是竞价中的竞价不允许暂停');
        }
        $admin = Auth::guard('admin')->user();
        BidBill::where('id', $id)->update([
            'bid_status' => 'H',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id
        ]);
        $leftTime = (new BidBillHallRepo)->getLeftTime($bidBill, $id);
        $pausedReason = Sub::where('bid_bill_id', $id)->value('paused_reason');

        $flag = Sub::where('bid_bill_id', $id)
                ->update([
            'paused_at' => date('Y-m-d H:i:s'),
            'bid_rest_at' => $leftTime,
            'paused_by' => $admin->user_id,
            'paused_reason' => !empty($pausedReason) ? $pausedReason . '，' . $request->reason : $request->reason,
        ]);
        BidBillSupplier::where('bid_bill_id', $id)
                ->where('entry_status', 'M')
                ->update([
                    'entry_status' => 'P',
        ]);
        $lastQuoteDate = BidBillQuote::where('bid_bill_id', $id)
                ->orderBy('quote_date', 'DESC')
                ->value('quote_date');
        wsSendMsg($id, 'stop', [
            'leftTime' => $leftTime,
            'lastQuoteTime' => $lastQuoteDate,
            'message' => $request->reason,
        ]);
        return $flag;
    }

    /**
     * 竞价终止
     * @param int $id
     */
    public function termination(int $id, Request $request) {
        $bidBill = BidBill::where('id', $id)
                ->select('enroll_date', 'bid_status', 'bill_status', 'cfm_status', 'org_id')
                ->first();
        if (empty($bidBill)) {
            check(false, '竞价单不存在');
        }

        $terminate = !empty($request->audit) ? $request->audit : $request->terminate; //定标意见
        $admin = Auth::guard('admin')->user();
        if ($bidBill->bill_status != 'C') {
            check(false, '不是已审核的竞价不允许终止');
        }
        if ($bidBill->bid_status == 'E') {
            check(false, '已定标的竞价不能终止');
        }
        if ($bidBill->bid_status == 'G') {
            check(false, '已终止的竞价不能终止');
        }
        $data = [
            'bid_status' => 'G',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id
        ];
        if ($bidBill->cfm_status === 'A') {
            $data['cfm_status'] = 'C';
        }
        $flag = BidBill::where('id', $id)->update($data);
        BidBillSupplier::where('bid_bill_id', $id)
                ->where('allow_bid', 1)
                ->update([
                    'entry_status' => 'S',
        ]);
        $subData = [
            'terminate' => $terminate,
            'terminate_by' => $admin->user_id,
            'terminate_at' => date('Y-m-d H:i:s'),
        ];
        if (in_array($bidBill->bid_status, ['J', 'C', 'D'])) {
            $subData['finished_at'] = date('Y-m-d H:i:s');
        }
        Sub::where('bid_bill_id', $id)
                ->update($subData);
        if ($bidBill->bid_status == 'C') {
            $lastQuoteDate = BidBillQuote::where('bid_bill_id', $id)
                    ->orderBy('quote_date', 'DESC')
                    ->value('quote_date');
            wsSendMsg($id, 'termination', [
                'leftTime' => null,
                'lastQuoteTime' => $lastQuoteDate,
                'message' => $terminate,
            ]);
        }
        return $flag;
    }

    /**
     * 竞价结束
     * @param int $id
     */
    public function finished(int $id, Request $request) {
        $bidBill = BidBill::where('id', $id)
                ->select('id','enroll_date', 'bid_status', 'person_id', 'name', 'org_id')
                ->first();
//        $supplierId = $request->supplier_id;
        $finishedReason = $request->finished_reason; //定标意见
        $admin = Auth::guard('admin')->user();
        if (!in_array($bidBill->bid_status, ['C', 'H'])) {
            check(false, '不是竞价中或暂停的竞价不允许结束');
        }
        $supplierIds = BidBillQuote::where('bid_bill_id', $id)
                ->orderBy('quote_date', 'DESC')
                ->pluck('supplier_id');
        if (!empty($supplierIds) && !empty($supplierIds->toArray())) {
            BidBillSupplier::where('bid_bill_id', $id)
                    ->where('entry_status', 'M')
                    ->whereIn('supplier_id', $supplierIds)
                    ->update([
                        'entry_status' => 'Q',
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => $admin->user_id
            ]);
            BidBillSupplier::where('bid_bill_id', $id)
                    ->where('entry_status', 'M')
                    ->whereNotIn('supplier_id', $supplierIds)
                    ->update([
                        'entry_status' => 'J',
            ]);
            $flag = BidBill::where('id', $id)->update([
                'bid_status' => 'D',
                'cfm_status' => 'B',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $admin->user_id
            ]);
        } else {
            BidBillSupplier::where('bid_bill_id', $id)
                    ->whereIn('entry_status', ['M', 'P'])
                    ->update([
                        'entry_status' => 'J',
            ]);
            $flag = BidBill::where('id', $id)->update([
                'bid_status' => 'G',
                'cfm_status' => 'C',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $admin->user_id
            ]);
        }
        Sub::where('bid_bill_id', $id)
                ->update([
                    'terminate' => '结束竞价时没有供应商报价',
                    'terminate_by' => $admin->user_id,
                    'terminate_at' => date('Y-m-d H:i:s'),
                    'finished_reason' => $finishedReason,
                    'finished_by' => $admin->user_id,
                    'finished_at' => date('Y-m-d H:i:s'),
        ]);
        $lastQuoteDate = BidBillQuote::where('bid_bill_id', $id)
                ->orderBy('quote_date', 'DESC')
                ->value('quote_date');
        wsSendMsg($id, 'finished', [
            'leftTime' => null,
            'lastQuoteTime' => $lastQuoteDate,
            'message' => $finishedReason,
        ]);
        if (!empty($supplierIds)) {
            $bossUrl = env('BOSS_URL');
            $this->sendFinished($bossUrl, $bidBill);
        }
        return $flag;
    }

    public function sendFinished($bossUrl, $bidBill) {

        $messageId = Message::insertGetId([
                    'receiver_type' => 'PURCHASER',
                    'content_url' => $bossUrl . '/front/#/bidding/BiddingDetails?id=' . $bidBill['id'],
                    'sender_id' => 1,
                    'message_type' => 'SYSTEM',
                    'message_title' => '竞价评标通知',
                    'message' => '【' . $bidBill['name'] . '】竞价已结束，请及时完成评标。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);

        MessageReceiver::insert([
            'message_id' => $messageId,
            'receiver_id' => $bidBill['person_id'],
            'org_id' => $bidBill['org_id'],
            'read_flag' => 'N',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        if (empty($bidBill['email'])) {
            return;
        }

        $email = User::whereIn('user_id', $bidBill['person_id'])
                ->where('enable', '1')
                ->where('deleted_flag', 'N')
                ->value('email');
        if (empty($email)) {
            return;
        }
        $response = Mail::mailer('default')
                ->send('mail.bidbillToFinished', $bidBill, function (MailMessage $message) use ($email) {
            $message->to($email);
            $message->subject('【' . env('APP_NAME') . '】竞价评标通知');
        });

        SendLog::insert([
            'type' => 'BIDBILL_BEABOUT',
            'message_to' => $bidBill['email'],
            'title' => '竞价评标通知',
            'message' => json_encode($bidBill),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 竞价终止
     * @param int $id
     */
    public function decision(int $id, Request $request) {
        $bidBill = BidBill::where('id', $id)
                ->select('enroll_date', 'bid_status', 'deposit_flag', 'id', 'name', 'org_id')
                ->first();
        $supplierId = $request->supplier_id;
        $audit = $request->audit; //定标意见
        $amount = $request->amount; //中标金额
        $result = $request->result; //中标金额
        $admin = Auth::guard('admin')->user();
        $flag = BidBill::where('id', $id)->update([
            'bid_status' => 'E',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id
        ]);
        if ($bidBill->deposit_flag === 'Y') {
            BidBillPay::where('bid_bill_id', $id)->update([
                'return_status' => 'E',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $admin->user_id
            ]);
        }
        BidBillSupplier::where('bid_bill_id', $id)
                ->where('supplier_id', $supplierId)
                ->update([
                    'entry_status' => 'F',
                    'result' => $audit,
        ]);
        //中标站内信
        $bbs = BidBillSupplier::where('bid_bill_id', $id)->where('supplier_id', $supplierId)->first();
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/biddingManage/biddingDetail?id=' . $id,
                    'sender_id' => $bidBill['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . env('APP_NAME') . '】【' . $bidBill['name'] . '】竞价结果通知',
                    'message' => '恭喜你，【' . $bidBill['name'] . '】已中标。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        messageReceiver::insertGetId([
            'message_id' => $messageId,
            'receiver_id' => $bbs['enroll_id'],
            'supplier_id' => $supplierId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        //中标邮件
        $email = User::where('user_id', $bbs['enroll_id'])
                ->where('deleted_flag', 'N')
                ->where('user_type', 'SUPPLIER')
                ->value('email');
        if ($email) {
            $sent = new SendMailJob(['email' => $email, 'title' => $bidBill['name'], 'id' => $id], 'BIDBILLPASS');
            $sent->handle();
            //(new SendLogRepo)->BidBillPass($email, $bidBill['name'],$id);
        }
        BidBillSupplier::where('bid_bill_id', $id)
                ->whereNot('supplier_id', $supplierId)
                ->where('allow_bid', 1)
                ->whereNot('entry_status', 'J')
                ->update([
                    'entry_status' => 'G',
                    'result' => $result,
        ]);
        //未中标站内信
        $SupplierObj = BidBillSupplier::where('bid_bill_id', $id)
                ->whereNot('supplier_id', $supplierId)
                ->where('allow_bid', 1)
                ->whereNot('entry_status', 'J')
                ->get();
        if (!empty($SupplierObj)) {
            $SupplierList = $SupplierObj->toArray();
            foreach ($SupplierList as $ietm) {
                $messageId = Message::insertGetId([
                            'receiver_type' => 'SUPPLIER',
                            'content_url' => $bossUrl . '/front/#/biddingManage/biddingDetail?id=' . $id,
                            'sender_id' => $bidBill['org_id'],
                            'message_type' => 'SYSTEM',
                            'message_title' => '【' . env('APP_NAME') . '】【' . $bidBill['name'] . '】竞价结果通知',
                            'message' => '很遗憾，【' . $bidBill['name'] . '】未中标。',
                            'created_at' => date('Y-m-d H:i:s'),
                ]);
                messageReceiver::insertGetId([
                    'message_id' => $messageId,
                    'receiver_id' => $ietm['enroll_id'],
                    'supplier_id' => $ietm['supplier_id'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                //未中标邮件
                $email = User::where('user_id', $ietm['enroll_id'])
                        ->where('deleted_flag', 'N')
                        ->where('user_type', 'SUPPLIER')
                        ->value('email');
                if ($email) {
                    $sent = new SendMailJob(['email' => $email, 'title' => $bidBill['name'], 'id' => $id], 'BIDBILLREFUSE');
                    $sent->handle();
                    //(new SendLogRepo)->BidBillRefuse($email, $bidBill['name'],$id);
                }
            }
        }

        Sub::where('bid_bill_id', $id)
                ->update([
                    'amount' => $amount,
                    'supplier_id' => $supplierId,
                    'audit' => $audit,
                    'decider_by' => $admin->user_id,
                    'decider_at' => date('Y-m-d H:i:s')
        ]);
        $this->noticecfm($id);
        //$this->sends($bidBill->toArray(), $id);
        return $flag;
    }

    /**
     * 竞价开始
     * @param int $id
     */
    public function begin(int $id) {
        $bidBill = BidBill::where('id', $id)->select('enroll_date', 'bid_status', 'open_date', 'bid_time')->first();
        if ($bidBill->bid_status !== 'H') {
            check(false, '不是暂停状态的竞价不能开始');
        }
        //不是报名截止的竞价单不允许资审
        $admin = Auth::guard('admin')->user();
        $time = date('Y-m-d H:i:s');
        BidBill::where('id', $id)->update([
            'bid_status' => 'C',
            'updated_at' => $time,
            'updated_by' => $admin->user_id
        ]);
        $bidRestAt = Sub::where('bid_bill_id', $id)->value('bid_rest_at');
        $flag = Sub::where('bid_bill_id', $id)
                ->update([
            'pause_start_at' => $time
        ]);
        $lastQuoteDate = BidBillQuote::where('bid_bill_id', $id)
                ->orderBy('quote_date', 'DESC')
                ->value('quote_date');
        BidBillSupplier::where('bid_bill_id', $id)
                ->where('entry_status', 'P')
                ->update([
                    'entry_status' => 'M',
        ]);
        wsSendMsg($id, 'begin', [
            'leftTime' => $bidRestAt,
            'lastQuoteTime' => $lastQuoteDate,
            'message' => null,
        ]);
        return $flag;
    }

    /**
     * 收取保证金
     * @param Request $request
     */
    public function pay(int $id, Request $request) {
        $suppliers = $request->suppliers;
        if (empty($suppliers)) {
            check(false, '请选择收取保证金的供应商');
        }
        $bidBill = BidBill::where('id', $id)
                ->select('enroll_date', 'bid_status', 'check_type', 'deposit_flag', 'cash_deposit')
                ->first();
        if (empty($bidBill)) {
            check(false, '比价单不存在');
        }
        if ($bidBill->bid_status === 'K') {
            check(false, '已收取保证金');
        }
        if ($bidBill->check_type == '1' && !in_array($bidBill->bid_status, ['L', 'B'])) {
            check(false, '先通过资审，再收取保证金');
        }

        if ($bidBill->check_type == '3' && !in_array($bidBill->bid_status, ['L', 'B', 'I'])) {
            check(false, '比价单当前状态不是待收保证金，无法收取保证金');
        }
        if ($bidBill && $bidBill->check_type == '3' && $bidBill->bid_status === 'I') {
            $quoteCount = BidBillSupplier::where('bid_bill_id', $id)
                    ->where('entry_status', 'Y')
                    ->count();
            if (!empty($bidBill->bid_number) && $bidBill->bid_number > $quoteCount) {
                check(false, '无供应商报名，无需收取保证金');
            }
        }
        if ($bidBill->deposit_flag == 'N') {
            check(false, '无需收取保证金');
        }
//供应商当前状态不是资审通过，无法收保证金
        $admin = Auth::guard('admin')->user();
        $supplierIds = array_column($suppliers, 'supplier_id');
        $bidSuppliers = BidBillSupplier::where('bid_bill_id', $id)
                ->select('supplier_id', 'entry_status')
                ->whereIn('supplier_id', $supplierIds)
                ->get()
                ->toArray();
        $paySupplierIds = [];
        $paySupplierObj = BidBillPay::selectRaw('bill_status,supplier_id')
                ->where('bid_bill_id', $id)
                ->whereIn('supplier_id', $supplierIds)
                ->get();
        if ($paySupplierObj) {
            $paySuppliers = $paySupplierObj->toArray();
            $paySupplierIds = array_column($paySuppliers, 'supplier_id');
            $paySupplierStatus = array_column($paySuppliers, 'bill_status', 'supplier_id');
        }
        $supplierArr = array_column($bidSuppliers, 'entry_status', 'supplier_id');

        $dataList = [];
        $payList = [];
        $allowSupplierIds = [];
        foreach ($suppliers as $supplier) {
            if (empty($supplier['entry_status']) || $supplier['entry_status'] === 'L') {
                continue;
            }
            $supplierId = $supplier['supplier_id'];
            $entryStatus = !empty($supplierArr[$supplierId]) ? $supplierArr[$supplierId] : null;
            $allowBid = $supplier['entry_status'] === 'D' ? 1 : 0;
            $entryRealStatus = $this->getEStatus($entryStatus, $supplier);
            $dataList[] = [
                'pay_date' => in_array($entryRealStatus, ['D', 'K']) ? date('Y-m-d H:i:s') : null,
                'bid_bill_id' => $id,
                'supplier_id' => $supplierId,
                'pay_id' => in_array($entryRealStatus, ['D', 'K']) ? $admin->user_id : null,
                'entry_status' => $entryRealStatus,
                'allow_bid' => $allowBid,
            ];
            if (in_array($supplierId, $paySupplierIds)) {
                $payList[] = [
                    'bid_bill_id' => $id,
                    'supplier_id' => $supplierId,
                    'bill_status' => $this->getPayStatus($supplier, $supplierId, $paySupplierStatus),
                    'audited_by' => in_array($entryRealStatus, ['D', 'K']) ? $admin->user_id : null,
                    'audited_at' => in_array($entryRealStatus, ['D', 'K']) ? date('Y-m-d H:i:s') : null,
                ];
            }
            $allowBid === 1 ? $allowSupplierIds[] = $supplierId : null;
        }
        if (!empty($payList)) {
            BidBillPay::upsert($payList, ['bid_bill_id',
                'supplier_id'], ['audited_at',
                'audited_by',
                'bill_status']);
        }
        $flag = BidBillSupplier::upsert($dataList, ['bid_bill_id',
                    'supplier_id'], ['pay_date',
                    'pay_id',
                    'entry_status',
                    'allow_bid']);
        $count = BidBillSupplier::where('bid_bill_id', $id)
                ->whereIn('entry_status', ['L', 'B', 'WQR'])
                ->count();
        if ($count > 0) {
            return $flag;
        }
        BidBill::where('id', $id)->update([
            'bid_status' => 'K',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id
        ]);
        return $flag;
    }

    public function getEStatus($entryStatus, $supplier) {
        switch ($supplier['entry_status']) {
            case 'L':
                return !empty($entryStatus) ? $entryStatus : 'L';
            case 'D':
                return 'D';
            case 'H':
                return 'K';
            default :
                return !empty($entryStatus) ? $entryStatus : 'L';
        }
    }

    public function getPayStatus($supplier, $supplierId, $paySupplierStatus) {
        if (empty($supplier['entry_status']) && !empty($paySupplierStatus[$supplierId])) {
            return $paySupplierStatus[$supplierId];
        } elseif (empty($supplier['entry_status'])) {
            return 'A';
        }
        switch (strtoupper(trim($supplier['entry_status']))) {
            case 'L':
                return !empty($paySupplierStatus[$supplierId]) ? $paySupplierStatus[$supplierId] : 'A';
            case 'D':
                return 'C';
            case 'H':
                return 'D';
        }
    }

    /**
     * 退还保证金
     * @param Request $request
     */
    public function returnDeposit(int $id, Request $request) {
        $suppliers = $request->suppliers;
        if (empty($suppliers)) {
            check(false, '请选择退还保证金的供应商');
        }
        $bidBill = BidBill::where('id', $id)->select('enroll_date', 'bid_status')->first();
        if (!in_array($bidBill->bid_status, ['E', 'M'])) {
            check(false, '未定标，无法退保证金');
        }
//供应商当前状态不是资审通过，无法收保证金
        $admin = Auth::guard('admin')->user();

        foreach ($suppliers as $supplier) {
            BidBillPay::where('bid_bill_id', $id)->where('supplier_id', $supplier['supplier_id'])->update([
                'return_status' => !empty($supplier['return_status']) ? $supplier['return_status'] : 'E',
                'return_date' => date('Y-m-d H:i:s'),
                'return_by' => $admin->user_id
            ]);
            if ($supplier['return_status'] === 'F') {
                BidBillSupplier::where('bid_bill_id', $id)
                        ->where('supplier_id', $supplier['supplier_id'])
                        ->update([
                            'return_date' => date('Y-m-d H:i:s'),
                            'return_id' => $admin->user_id
                ]);
            }
        }
        return true;
    }

    /**
     * 退还保证金
     * @param Request $request
     */
    public function returns(int $id, Request $request) {
        $bidBill = (new BidBill)->getTable();
        $supplier = (new BidBillSupplier)->getTable();
        $pay = (new BidBillPay)->getTable();
        $query = BidBillSupplier::from($supplier . ' as s')
                ->leftJoin($pay . ' AS ps', function ($join) {
                    $join->on('s.bid_bill_id', '=', 'ps.bid_bill_id')
                    ->on('s.supplier_id', '=', 'ps.supplier_id');
                })
                ->join($bidBill . ' as  bb', function($join) {
                    $join->on('bb.id', '=', 's.bid_bill_id');
                })
                ->selectRaw('ps.id,bb.org_id,ps.bid_bill_id,bb.`name`,bb.bill_no,s.supplier_id,'
                        . 'ps.bill_status AS pay_status,s.entry_status,ps.remark,'
                        . 'ps.sure_amount,ps.real_amount,ps.pay_date,ps.pay_id,'
                        . 's.return_id,s.return_date,ps.return_status,ps.return_certificate,ps.return_certificate_name,'
                        . 'ps.contact_name,ps.contact_phone,ps.certificate,ps.certificate_name')
                ->where('bb.deposit_flag', 'Y');
        $query->where('ps.bill_status', 'C');
        $query->where('ps.return_status', 'E');
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $query->orderBy('ps.pay_date', 'DESC');
        $object = $query->orderBy('s.id', 'ASC')->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['sure_amount'] = number_format($item['sure_amount'], 2, '.', '');
            $item['real_amount'] = number_format($item['real_amount'], 2, '.', '');
            $item['pay_status_name'] = $this->getPayStatusText($item['pay_status']);
            $item['return_status_name'] = $this->getReturnStatusText($item['return_status']);
            $item['entry_status_name'] = $this->getEntryStatusText($item['entry_status']);
        }
        (new SupplierBaseRepo)->setSuppliers($data);
        (new SubRepo)->setSubs($data, 'id');
        (new UserRepo)->setUsers($data, 'pay_id', 'pay_name');
        (new UserRepo)->setUsers($data, 'return_id', 'return_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function start(int $id) {
        $admin = Auth::guard('admin')->user();
        $bidBill = BidBill::where('id', $id)
                ->select('enroll_date', 'bid_status', 'deposit_flag', 'check_type')
                ->first();
        if ($bidBill->deposit_flag === 'Y' && $bidBill->bid_status !== 'K') {
            check(false, '未收取保证金的竞价不能启动');
        } elseif ($bidBill->deposit_flag === 'N' && $bidBill->check_type === '1' && $bidBill->bid_status !== 'B') {
            check(false, '未资审的竞价不能启动');
        } elseif ($bidBill->deposit_flag === 'N' && $bidBill->check_type === '3' && $bidBill->bid_status !== 'I') {
            check(false, '不是报名截止竞价不能启动');
        } elseif ($bidBill->deposit_flag === 'N' && $bidBill->check_type === '3' && $bidBill->bid_status === 'I') {
            $quoteCount = BidBillSupplier::where('bid_bill_id', $id)
                    ->where('entry_status', 'Y')
                    ->count();
            if (!empty($bidBill->bid_number) && $bidBill->bid_number > $quoteCount) {
                check(false, '不满足供应商报名条件，不能启动竞价');
            }
        } else {
            $quoteCount = BidBillSupplier::where('bid_bill_id', $id)
                    ->where('allow_bid', '1')
                    ->count();
            if (!empty($bidBill->bid_number) && $bidBill->bid_number > $quoteCount) {
                check(false, '不满足供应商报名条件，不能启动竞价');
            }
        }
        if (!empty($bidBill->open_date) && $bidBill->open_date > date('Y-m-d H:I:s')) {
            check(false, '未到竞价启动时间，不能启动竞价');
        }
        $flag = BidBill::where('id', $id)->update([
            'bid_status' => 'C',
            'cfm_status' => 'A',
            'open_date' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id
        ]);
        BidBillSupplier::where('bid_bill_id', $id)
                ->where('allow_bid', '1')
                ->update([
                    'entry_status' => 'M',
        ]);
        return $flag;
    }

    public function winning(int $id) {
        $name = BidBill::where('id', $id)->value('name');
        $object = BidBillSupplier::select()
                ->where('bid_bill_id', $id)
                ->where('allow_bid', 1)
                ->where('ranking', '>', 0)
                ->orderBy('ranking', 'ASC')
                ->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $data['amount'] = number_format($data['amount'], 2, '.', '');
        (new SupplierBaseRepo)->setSupplier($data, 'supplier_id');
        (new SupplierContactRepo)->setDefaultContact($data, 'supplier_id');
        $data['name'] = $name;
        return $data;
    }

    public function notice($bidBillId) {
        $bidBill = BidBill::where('id', $bidBillId)->first()->toArray();
        $materialList = BidBillEntry::where('deleted_flag', 'N')
                ->where('bid_bill_id', $bidBillId)
                ->get()
                ->toArray();
        (new PaycondRepo)->setPaycond($bidBill, 'pay_cond_id');
        (new UnitRepo)->setUnits($materialList, 'unit_id', 'unit_name');
        $user = User::where('user_id', $bidBill['person_id'])->where('deleted_flag', 'N')->first();
        $content = [
            'bidbill_title' => $bidBill['name'],
            'bill_no' => $bidBill['bill_no'],
            'bill_date' => date('Y-m-d', strtotime($bidBill['bill_date'])),
            'deli_date' => !empty($bidBill['deli_date']) ? date('Y-m-d', strtotime($bidBill['deli_date'])) : null,
            'deli_addr' => $bidBill['deli_addr'],
            'due_date' => date('Y-m-d H:i:s', strtotime($bidBill['enroll_date'])),
            'paycond_name' => $bidBill['paycond_name'],
            'bid_time' => $bidBill['bid_time'],
            'person_name' => $user->realname,
            'person_phone' => $user->phone,
            'materials' => $materialList,
        ];
        $data = [
            'biz_type' => 3,
            'due_date' => date('Y-m-d H:i:s', strtotime($bidBill['enroll_date'])),
            'org_id' => $bidBill['org_id'],
            'bill_no' => (new NoticeManageRepo)->getNoticeNo(),
            'src_bill_id' => $bidBillId,
            'src_bill_type' => 'sou_bidbill',
            'sup_scope' => $bidBill['biz_type'],
            'bill_status' => 'C',
            'bill_type_id' => 0,
            'src_bill_no' => '竞价发布：' . $bidBill['bill_no'],
            'org_id' => $bidBill['org_id'],
            'title' => $bidBill['name'] . '-竞价采购公告',
        ];
        $data['content'] = view('tpl.bidbill_notice', $content)->toHtml();
        return $data;
    }

    public function noticecfm($bidBillId) {
        $bidBill = BidBill::where('id', $bidBillId)->first()->toArray();
        $materialList = BidBillEntry::where('deleted_flag', 'N')
                ->where('bid_bill_id', $bidBillId)
                ->get()
                ->toArray();
        $obj = Purchaser::where('id', $bidBill['org_id'])
                ->where('deleted_flag', 'N')
                ->where('enable', 1)
                ->first();
        $bidbillSub = Sub::where('bid_bill_id', $bidBillId)
                ->first()
                ->toArray();
        (new PaycondRepo)->setPaycond($bidBill, 'pay_cond_id');
        (new UnitRepo)->setUnits($materialList, 'unit_id', 'unit_name');
        (new SupplierBaseRepo)->setSupplier($bidbillSub, 'supplier_id', 'supplier_name');
        $user = User::where('user_id', $bidBill['person_id'])->where('deleted_flag', 'N')->first();
        $content = [
            'bidbill_title' => $bidBill['name'],
            'bill_no' => $bidBill['bill_no'],
            'bill_date' => date('Y-m-d', strtotime($bidBill['bill_date'])),
            'deli_date' => !empty($bidBill['deli_date']) ? date('Y-m-d', strtotime($bidBill['deli_date'])) : null,
            'deli_addr' => $bidBill['deli_addr'],
            'due_date' => date('Y-m-d H:i:s', strtotime($bidBill['enroll_date'])),
            'bid_time' => $bidBill['bid_time'],
            'supplier_name' => $bidbillSub['supplier_name'],
            'person_name' => $user->realname,
            'person_phone' => $user->phone,
            'materials' => $materialList,
        ];
        $data = [
            'biz_type' => 'B',
            'due_date' => date('Y-m-d H:i:s', strtotime($bidBill['enroll_date'])),
            'bill_no' => (new NoticeManageRepo)->getNoticeNo(),
            'src_bill_id' => $bidBillId,
            'src_bill_type' => 'sou_bidbillcfm',
            'bill_status' => 'C',
            'sup_scope' => $bidBill['biz_type'],
            'bill_type_id' => 0,
            'src_bill_no' => '竞价定标：' . $bidBill['bill_no'],
            'title' => $bidBill['name'] . '-竞价结果公告',
        ];
        $data['content'] = view('tpl.bidbillcfm_notice', $content)->toHtml();
        $nrequest = (new Request);
        $nrequest->merge($data);
        (new NoticeManageRepo)->addData($nrequest);
        return $data;
    }

    public function sendcmfs($bidBillData, $bidBillId) {
        if ($bidBillData['bill_status'] !== 'C') {
            return;
        }

        $supplierIds = BidBillSupplier::where('bid_bill_id', $bidBillId)
                ->where('deleted_flag', 'N')
                ->pluck('supplier_id');
        if (empty($supplierIds)) {
            return [];
        }
        $orgName = Purchaser::where('id', $bidBillData['org_id'])
                ->value('name');
        $this->sendMail($bidBillData, $supplierIds, $orgName);
        $this->sendMessageCmf($bidBillData, $bidBillId, $supplierIds, $orgName);
    }

    public function sendMessageCmf($bidBillData, $bidBillId, $supplierIds) {

        $user = (new User)->getTable();
        $supplier = (new BidBillSupplier)->getTable();
        $bossUrl = env('BOSS_URL');

        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/biddingManage/biddingDetail?id=' . $bidBillId,
                    'sender_id' => $bidBillData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '竞价结果通知',
                    'message' => '【' . $bidBillData->name . '】的竞价项目邀请您报名',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userObj = User::from($user . ' as u')
                ->join($supplier . ' as us', function ($join) {
                    $join->on('u.user_id', '=', 'us.enroll_id');
                })
                ->whereIn('us.bid_bill_id', $bidBillId)
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
                'org_id' => $bidBillData['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            MessageReceiver::insert($dataList);
        }
    }

    public function sendMailCmf($bidBillData, $supplierIds, $orgName) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'bidBillData' => $bidBillData,
            'supplierIds' => $supplierIds,
            'orgName' => $orgName
                ], 'BIDBILLCMF'));
    }

}
