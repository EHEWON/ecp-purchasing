<?php

namespace App\Modules\Admin\Repository\Inquiry;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Compare\Compare,
    Inquiry\Entry,
    Inquiry\Inquiry,
    Inquiry\Sub,
    Inquiry\Supplier as InquirySupplier,
    Inquiry\TurnsLog,
    Message,
    MessageReceiver,
    NoticeSub,
    Purchaser,
    Quote\Quote,
    User,
    UserSupplier
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    Compare\CompareRepo,
    CurrencyRepo,
    Inquiry\EntryRepo,
    Inquiry\TurnsLogRepo,
    NoticeManageRepo,
    OrgRepo,
    PaycondRepo,
    SettleMentTypeRepo,
    UnitRepo,
    UserRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Redis
};
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};
use ZipArchive;

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

    public function __construct() {
        $this->model = new Inquiry();
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
    public function getList(Request $request, $filed = 'id,bill_no,title,related_no,'
    . 'biz_status,bill_status,bill_date,sup_scope,org_id,turns,open_type,tax_cal_type,'
    . 'end_date,person_id,turns,created_by,created_at,updated_at') {
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
            $item['biz_status_name'] = $this->getBizStatusText($item['biz_status']);
            $item['sup_scope_name'] = $this->getSupScopeText($item['sup_scope']);
            $item['open_type_name'] = $this->getOpenTypeText($item['open_type']);
            $item['tax_cal_type_name'] = $this->getTaxCalTypeText($item['tax_cal_type']);
            $item['turns_name'] = $this->getTurnsText($item['turns']);
            $item['bill_date'] = date('Y-m-d', strtotime($item['bill_date']));
        }
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new OrgRepo)->setOrgs($data, 'org_id', 'org_name');
        (new CompareRepo)->setCompareIds($data, 'id');
        (new SubRepo)->setQuoteNums($data, 'id');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function getTotal(Request $request) {
        $query = Inquiry::where('deleted_flag', 'N');
        $this->getWhere($query, $request);
        return $query->count();
    }

    public function getTotalByDate(Request $request) {
        $query = Inquiry::where('deleted_flag', 'N')
                ->where('bill_status', 'C');
        $this->getWhere($query, $request);
        switch (strtolower($request->createtype)) {
            case 'today':
                $object = $query->selectRaw('SUBSTR(bill_date,12,2) AS billdate,count(id) AS num')
                        ->groupBy(DB::Raw('SUBSTR(bill_date,12,2)'))
                        ->orderBy('billdate', 'ASC')
                        ->get();
                break;
            default :
                $object = $query->selectRaw('SUBSTR(bill_date,1,10) AS billdate,count(id) AS num')
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
                $ath = date('H');
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

    public function todo() {
        $query = Inquiry::where('deleted_flag', 'N')
                ->where('bill_status', 'A');
        return $query->count();
    }

    public function compareTodo() {
        $query = Inquiry::where('deleted_flag', 'N')
                ->where('bill_status', 'C')
                ->where('biz_status', 'B');
        return $query->count();
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {
        $query = $this->model->selectRaw('id,bill_no,related_no,title,bill_date,deli_date,deli_addr,'
                . 'org_id,person_id,open_type,phone,settle_type_id,curr_id,person_id,created_by,created_at,'
                . 'loc_curr_id,inv_type,sup_scope,end_date,date_from,date_to,bill_status,tax_cal_type,'
                . 'payment_terms,settlement_method,total_inquiry,remark,biz_status,turns,stopped_reason,'
                . 'stopped_by,stopped_at,other_pay_terms,other_pay_terms_info,updated_at');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = [];
        $base = $object->toArray();
        $base['bill_date'] = date('Y-m-d', strtotime($base['bill_date']));
        $base['deli_date'] = !empty($base['deli_date']) ? date('Y-m-d', strtotime($base['deli_date'])) : null;
        $base['bill_status_name'] = $this->getBillStatusText($base['bill_status']);
        $base['biz_status_name'] = $this->getBizStatusText($base['biz_status']);
        $base['sup_scope_name'] = $this->getSupScopeText($base['sup_scope']);
        $base['open_type_name'] = $this->getOpenTypeText($base['open_type']);
        $base['tax_cal_type_name'] = $this->getTaxCalTypeText($base['tax_cal_type']);
        $base['turns_name'] = $this->getTurnsText($base['turns']);
        (new UserRepo)->setUser($base, 'created_by', 'created_name');
        (new UserRepo)->setUser($base, 'person_id', 'person_name');
        (new UserRepo)->setUser($base, 'stopped_by', 'stopped_name');
        (new CurrencyRepo)->setCurrency($base, 'curr_id', 'curr_name');
        (new CurrencyRepo)->setCurrency($base, 'loc_curr_id', 'loc_curr_name');
        (new PaycondRepo)->setPaycond($base, 'payment_terms');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settle_type_id', 'settle_type_name');
        (new CompareRepo)->setCompareId($base, 'id');
        (new OrgRepo)->setOrg($base, 'org_id', 'org_name');
        $data['base'] = $base;
        $base['inv_type_name'] = $this->getInvtypeText($base['inv_type']);
        $data['attachs'] = (new AttachRepo)->getList($id);
        $data['entrys'] = (new EntryRepo)->getList($id, $base['turns']);
        $quoteNum = 0;
        $data['suppliers'] = (new SupplierRepo)->supplierList($id, $base['biz_status_name'], $base['biz_status'], $quoteNum);
        $data['base']['quote_num'] = $quoteNum;
        $data['turns_logs'] = (new TurnsLogRepo)->getList($id);
        $data['results'] = (new SubRepo)->info($id);
        $compare = Compare::where('deleted_flag', 'N')->where('inquiry_id', $id)
                ->selectRaw('id,bill_no,bill_date,bill_status')
                ->first();
        $data['compare'] = $compare;
        return $data;
    }

    /**
     * @param int $inquiryId
     * @param Request $request
     * 
     * @return array
     */
    public function edited($inquiryId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $base = $request->base;
        $inquiryData = [
            'related_no' => !empty($base['related_no']) ? $base['related_no'] : null,
            'title' => !empty($base['title']) ? $base['title'] : '',
            'bill_date' => date('Y-m-d'),
            'deli_date' => !empty($base['deli_date']) ? $base['deli_date'] : null,
            'deli_addr' => !empty($base['deli_addr']) ? $base['deli_addr'] : null,
            'req_org_id' => !empty($base['req_org_id']) ? $base['req_org_id'] : null,
            'org_id' => !empty($base['org_id']) ? $base['org_id'] : 1,
            'rcv_org_id' => !empty($base['rcv_org_id']) ? $base['rcv_org_id'] : null,
            'settle_org_id' => !empty($base['settle_org_id']) ? $base['settle_org_id'] : null,
            'pay_org_id' => !empty($base['pay_org_id']) ? $base['pay_org_id'] : null,
            'person_id' => !empty($base['person_id']) ? $base['person_id'] : null,
            'phone' => !empty($base['phone']) ? $base['phone'] : '',
            'settle_type_id' => !empty($base['settle_type_id']) ? $base['settle_type_id'] : null,
            'curr_id' => !empty($base['curr_id']) ? $base['curr_id'] : 1,
            'loc_curr_id' => !empty($base['loc_curr_id']) ? $base['loc_curr_id'] : 1,
            'exch_type_id' => !empty($base['exch_type_id']) ? $base['exch_type_id'] : null,
            'exch_rate' => !empty($base['exch_rate']) ? $base['exch_rate'] : null,
            'tax_type' => !empty($base['tax_type']) ? $base['tax_type'] : null,
            'inv_type' => !empty($base['inv_type']) ? $base['inv_type'] : null,
            'sum_amount' => !empty($base['sum_amount']) ? $base['sum_amount'] : null,
            'sum_tax' => !empty($base['sum_tax']) ? $base['sum_tax'] : null,
            'sum_tax_amount' => !empty($base['sum_tax_amount']) ? $base['sum_tax_amount'] : null,
            'sum_qty' => !empty($base['sum_qty']) ? $base['sum_qty'] : null,
            'bill_status' => !empty($base['bill_status']) ? $base['bill_status'] : 'A',
            'cfm_status' => !empty($base['cfm_status']) ? $base['cfm_status'] : null,
            'biz_partner_id' => !empty($base['biz_partner_id']) ? $base['biz_partner_id'] : null,
            'sup_scope' => !empty($base['sup_scope']) ? $base['sup_scope'] : null,
            'end_date' => !empty($base['end_date']) ? $base['end_date'] : null,
            'date_from' => !empty($base['date_from']) ? $base['date_from'] : null,
            'date_to' => !empty($base['date_to']) ? $base['date_to'] : null,
            'bill_type_id' => !empty($base['bill_type_id']) ? $base['bill_type_id'] : null,
            'biz_model' => !empty($base['biz_model']) ? $base['biz_model'] : null,
            'certificate' => !empty($base['certificate']) ? $base['certificate'] : null,
            'biz_addr' => !empty($base['biz_addr']) ? $base['biz_addr'] : null,
            'reg_capital' => !empty($base['reg_capital']) ? $base['reg_capital'] : null,
            'biz_status' => null,
            'open_type' => !empty($base['open_type']) ? $base['open_type'] : null,
            'total_inquiry' => !empty($base['total_inquiry']) ? $base['total_inquiry'] : 0,
            'publisher' => $admin->realname,
            'turns' => 1,
            'sup_quo_num' => !empty($base['sup_quo_num']) ? $base['sup_quo_num'] : null,
            'sup_curr_type' => !empty($base['sup_curr_type']) ? $base['sup_curr_type'] : null,
            'rate_date' => !empty($base['rate_date']) ? $base['rate_date'] : null,
            'remark' => !empty($base['remark']) ? $base['remark'] : null,
            'inquiry_title' => !empty($base['title']) ? $base['title'] : '',
            'delivery_method' => !empty($base['delivery_method']) ? $base['delivery_method'] : '',
            'purchaser_approva' => !empty($base['purchaser_approva']) ? $base['purchaser_approva'] : '',
            'other_pay_terms' => !empty($base['other_pay_terms']) ? $base['other_pay_terms'] : '',
            'other_pay_terms_info' => !empty($base['other_pay_terms_info']) ? $base['other_pay_terms_info'] : '',
            'payment_terms' => !empty($base['payment_terms']) ? $base['payment_terms'] : null,
            'settlement_method' => !empty($base['settle_type_id']) ? $base['settle_type_id'] : null,
            'settlement_cur' => !empty($base['settlement_cur']) ? $base['settlement_cur'] : null,
            'tax_cal_type' => !empty($base['tax_cal_type']) ? $base['tax_cal_type'] : null,
            'exchange_rate_date' => !empty($base['exchange_rate_date']) ? $base['exchange_rate_date'] : null,
            'required_level' => !empty($base['required_level']) ? $base['required_level'] : '',
            'required_cat' => !empty($base['required_cat']) ? $base['required_cat'] : '',
            'is_filter' => !empty($base['is_filter']) ? $base['is_filter'] : '',
            'text_field' => !empty($base['text_field']) ? $base['text_field'] : '',
            'check_box_field' => !empty($base['check_box_field']) ? $base['check_box_field'] : '',
            'quoted_num' => !empty($base['quoted_num']) ? $base['quoted_num'] : 0,
            'delivery_date' => !empty($base['delivery_date']) ? $base['delivery_date'] : '',
            'warranty_period' => !empty($base['warranty_period']) ? $base['warranty_period'] : '',
            'business_type_id' => !empty($base['business_type_id']) ? $base['business_type_id'] : null,
            'source' => !empty($base['source']) ? $base['source'] : '',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id,
        ];
        $sub = [
            'creator_id' => $admin->user_id,
            'modifier_id' => $admin->user_id,
            'modify_time' => date('Y-m-d H:i:s'),
            'create_time' => date('Y-m-d H:i:s'),
            'inquiry_title' => !empty($sub['title']) ? $sub['title'] : '',
            'deleted_flag' => 'N',
        ];
        if (!empty($base['bill_status']) && in_array($base['bill_status'], ['B', 'C'])) {
            $inquiryData['bill_status'] = 'C';
            $inquiryData['biz_status'] = 'A';
            $sub['audit_date'] = date('Y-m-d H:i:s');
            $sub['auditor_id'] = $admin->user_id;
        }
        $sub['inquiry_id'] = $inquiryId;
        Sub::upsert($sub, ['inquiry_id'], ['deleted_flag',
            'modify_time',
            'modifier_id',
            'inquiry_title']);

        $flag = Inquiry::where('id', $inquiryId)->update($inquiryData);
//        (new SupplierCompanyRepo)->updateData($supplierId, $request);
//        (new SubRepo)->updateData($inquiryId, $request);
        (new AttachRepo)->updateData($inquiryId, $request);
        (new SupplierRepo)->updateData($inquiryId, $request);
        (new EntryRepo())->updateData($inquiryId, $request);

        if ($inquiryData['bill_status'] !== 'C') {
            return $flag;
        }
        TurnsLog::insert([
            'inquiry_id' => $inquiryId,
            'turns' => 1,
            'handler_id' => $admin->user_id,
            'handle_time' => date('Y-m-d H:i:s'),
            'log_dead_line' => !empty($base['end_date']) ? $base['end_date'] : null,
            'entry_log_scope' => !empty($base['sup_scope']) ? $base['sup_scope'] : 1,
            'note' => '首轮',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->sends($inquiryData, $inquiryId);
        return $flag;
    }

    public function sends($inquiryData, $inquiryId) {
        if ($inquiryData['bill_status'] !== 'C') {
            return;
        }
        $data = $this->notice($inquiryId);
        $nrequest = (new Request);
        $nrequest->merge($data);
        (new NoticeManageRepo)->addData($nrequest);
        if ($inquiryData['sup_scope'] == 1) {
            return true;
        }
        $supplierIds = InquirySupplier::where('inquiry_id', $inquiryId)
                ->where('deleted_flag', 'N')
                ->pluck('supplier_id');
        if (empty($supplierIds)) {
            return [];
        }
        $orgName = Purchaser::where('id', $inquiryData['org_id'])
                ->value('name');
        $this->sendMail($inquiryData, $supplierIds, $orgName, $inquiryId);
        $this->sendMessage($inquiryData, $inquiryId, $supplierIds, $orgName);
    }

    public function sendMessage($inquiryData, $inquiryId, $supplierIds, $orgName) {

        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/quoteManage/inquiryDetail?type=info&id=' . $inquiryId,
                    'sender_id' => $inquiryData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . env('APP_NAME') . '】已发布报价邀请',
                    'message' => '您好，已向您发布报价邀请，请尽快登录系统报价。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function($join) {
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
                'org_id' => $inquiryData['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            MessageReceiver::insert($dataList);
        }
    }

    public function sendMail($inquiryData, $supplierIds, $orgName, $inquiryId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'inquiryData' => $inquiryData,
            'supplierIds' => $supplierIds,
            'inquiryId' => $inquiryId,
            'orgName' => $orgName
                ], 'INQUIRY'));
    }

    /**
     * @param int $inquiryId
     * @param Request $request
     * 
     * @return array
     */
    public function add(Request $request) {
        $base = $request->base;
        $admin = Auth::guard('admin')->user();
        $inquiryNo = !empty($base['bill_no']) ? $base['bill_no'] : $this->getInquiryNo();
        $inquiryData = [
            'related_no' => !empty($base['related_no']) ? $base['related_no'] : null,
            'bill_no' => $inquiryNo,
            'title' => !empty($base['title']) ? $base['title'] : '',
            'bill_date' => date('Y-m-d'),
            'deli_date' => !empty($base['deli_date']) ? $base['deli_date'] : null,
            'deli_addr' => !empty($base['deli_addr']) ? $base['deli_addr'] : null,
            'req_org_id' => !empty($base['req_org_id']) ? $base['req_org_id'] : null,
            'org_id' => !empty($base['org_id']) ? $base['org_id'] : 1,
            'rcv_org_id' => !empty($base['rcv_org_id']) ? $base['rcv_org_id'] : null,
            'settle_org_id' => !empty($base['settle_org_id']) ? $base['settle_org_id'] : null,
            'pay_org_id' => !empty($base['pay_org_id']) ? $base['pay_org_id'] : null,
            'person_id' => !empty($base['person_id']) ? $base['person_id'] : null,
            'phone' => !empty($base['phone']) ? $base['phone'] : '',
            'settle_type_id' => !empty($base['settle_type_id']) ? $base['settle_type_id'] : null,
            'curr_id' => !empty($base['curr_id']) ? $base['curr_id'] : 1,
            'loc_curr_id' => !empty($base['loc_curr_id']) ? $base['loc_curr_id'] : 1,
            'exch_type_id' => !empty($base['exch_type_id']) ? $base['exch_type_id'] : null,
            'exch_rate' => !empty($base['exch_rate']) ? $base['exch_rate'] : null,
            'tax_type' => !empty($base['tax_type']) ? $base['tax_type'] : null,
            'inv_type' => !empty($base['inv_type']) ? $base['inv_type'] : null,
            'sum_amount' => !empty($base['sum_amount']) ? $base['sum_amount'] : null,
            'sum_tax' => !empty($base['sum_tax']) ? $base['sum_tax'] : null,
            'sum_tax_amount' => !empty($base['sum_tax_amount']) ? $base['sum_tax_amount'] : null,
            'sum_qty' => !empty($base['sum_qty']) ? $base['sum_qty'] : null,
            'bill_status' => !empty($base['bill_status']) ? $base['bill_status'] : 'A',
            'cfm_status' => !empty($base['cfm_status']) ? $base['cfm_status'] : null,
            'biz_partner_id' => !empty($base['biz_partner_id']) ? $base['biz_partner_id'] : null,
            'sup_scope' => !empty($base['sup_scope']) ? $base['sup_scope'] : 1,
            'end_date' => !empty($base['end_date']) ? $base['end_date'] : null,
            'date_from' => !empty($base['date_from']) ? $base['date_from'] : null,
            'date_to' => !empty($base['date_to']) ? $base['date_to'] : null,
            'bill_type_id' => !empty($base['bill_type_id']) ? $base['bill_type_id'] : null,
            'biz_model' => !empty($base['biz_model']) ? $base['biz_model'] : null,
            'certificate' => !empty($base['certificate']) ? $base['certificate'] : null,
            'biz_addr' => !empty($base['biz_addr']) ? $base['biz_addr'] : null,
            'reg_capital' => !empty($base['reg_capital']) ? $base['reg_capital'] : null,
            'biz_status' => null,
            'open_type' => !empty($base['open_type']) ? $base['open_type'] : null,
            'total_inquiry' => !empty($base['total_inquiry']) ? $base['total_inquiry'] : 0,
            'publisher' => $admin->realname,
            'turns' => 1,
            'sup_quo_num' => !empty($base['sup_quo_num']) ? $base['sup_quo_num'] : null,
            'sup_curr_type' => !empty($base['sup_curr_type']) ? $base['sup_curr_type'] : null,
            'rate_date' => !empty($base['rate_date']) ? $base['rate_date'] : null,
            'remark' => !empty($base['remark']) ? $base['remark'] : null,
            'inquiry_title' => !empty($base['title']) ? $base['title'] : '',
            'delivery_method' => !empty($base['delivery_method']) ? $base['delivery_method'] : '',
            'purchaser_approva' => !empty($base['purchaser_approva']) ? $base['purchaser_approva'] : '',
            'other_pay_terms' => !empty($base['other_pay_terms']) ? $base['other_pay_terms'] : '',
            'other_pay_terms_info' => !empty($base['other_pay_terms_info']) ? $base['other_pay_terms_info'] : '',
            'payment_terms' => !empty($base['payment_terms']) ? $base['payment_terms'] : null,
            'settlement_method' => !empty($base['settle_type_id']) ? $base['settle_type_id'] : null,
            'settlement_cur' => !empty($base['settlement_cur']) ? $base['settlement_cur'] : null,
            'tax_cal_type' => !empty($base['tax_cal_type']) ? $base['tax_cal_type'] : null,
            'exchange_rate_date' => !empty($base['exchange_rate_date']) ? $base['exchange_rate_date'] : null,
            'required_level' => !empty($base['required_level']) ? $base['required_level'] : '',
            'required_cat' => !empty($base['required_cat']) ? $base['required_cat'] : '',
            'is_filter' => !empty($base['is_filter']) ? $base['is_filter'] : '',
            'text_field' => !empty($base['text_field']) ? $base['text_field'] : '',
            'check_box_field' => !empty($base['check_box_field']) ? $base['check_box_field'] : '',
            'quoted_num' => !empty($base['quoted_num']) ? $base['quoted_num'] : 0,
            'delivery_date' => !empty($base['delivery_date']) ? $base['delivery_date'] : '',
            'warranty_period' => !empty($base['warranty_period']) ? $base['warranty_period'] : '',
            'business_type_id' => !empty($base['business_type_id']) ? $base['business_type_id'] : null,
            'source' => !empty($base['source']) ? $base['source'] : '',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $admin->user_id,
        ];
        $sub = [
            'creator_id' => $admin->user_id,
            'modifier_id' => $admin->user_id,
            'modify_time' => date('Y-m-d H:i:s'),
            'create_time' => date('Y-m-d H:i:s'),
            'inquiry_title' => !empty($sub['title']) ? $sub['title'] : '',
            'deleted_flag' => 'N',
        ];
        if (!empty($base['bill_status']) && in_array($base['bill_status'], ['B', 'C'])) {
            $inquiryData['bill_status'] = 'C';
            $inquiryData['biz_status'] = 'A';
            $sub['audit_date'] = date('Y-m-d H:i:s');
            $sub['auditor_id'] = $admin->user_id;
        }
        $inquiryId = Inquiry::insertGetId($inquiryData);
        $sub['inquiry_id'] = $inquiryId;
        Sub::upsert($sub, ['inquiry_id'], ['deleted_flag',
            'modify_time',
            'modifier_id',
            'inquiry_title']);
        (new AttachRepo)->updateData($inquiryId, $request);
        (new SupplierRepo)->updateData($inquiryId, $request);
        (new EntryRepo())->updateData($inquiryId, $request, 1);
        if ($inquiryData['bill_status'] !== 'C') {
            return ['id' => $inquiryId, 'inquiry_no' => $inquiryNo];
        }
        $this->sends($inquiryData, $inquiryId);
        TurnsLog::insert([
            'inquiry_id' => $inquiryId,
            'turns' => 1,
            'handler_id' => $admin->user_id,
            'handle_time' => date('Y-m-d H:i:s'),
            'log_dead_line' => !empty($base['end_date']) ? $base['end_date'] : null,
            'entry_log_scope' => !empty($base['sup_scope']) ? $base['sup_scope'] : 1,
            'note' => '首轮',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return ['id' => $inquiryId, 'inquiry_no' => $inquiryNo];
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        if (empty($ids)) {
            check(false, '请选择要删除的询单');
        }
        $admin = Auth::guard('admin')->user();
        $count = Inquiry::whereIn('id', $request->ids)
                ->where('deleted_flag', 'N')
                ->count();
        $countN = Inquiry::whereIn('id', $request->ids)
                ->where('deleted_flag', 'N')
                ->whereIn('bill_status', ['B', 'C'])
                ->count();
        $countP = Inquiry::whereIn('id', $request->ids)
                ->where('deleted_flag', 'N')
                ->whereIn('bill_status', ['A'])
                ->where(function($q)use ($admin) {
                    $q->where('person_id', $admin->user_id)
                    ->orWhere('created_by', $admin->user_id);
                })
                ->count();
        DB::beginTransaction();
        $flag = Inquiry::whereIn('id', $ids)
                ->where(function($q)use ($admin) {
                    $q->where('person_id', $admin->user_id)
                    ->orWhere('created_by', $admin->user_id);
                })
                ->where('bill_status', 'A')
                ->delete();
        $str = '';
        if (!empty($flag)) {
            $str .= '删除成功' . $flag . '条';
        }

        if (!empty($countN)) {
            $str .= (!empty($str) ? '，' : '') . '处于已提交或已审核不能删除的询单' . $countN . '条';
        }
        if (empty($countP)) {
            $str .= (!empty($str) ? '，' : '') . '不是询单创建人或采购员不能删除的询单' . $countN . '条';
        }
        DB::commit();
        check($count === $flag, $str);
        return $flag;
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
//                $user = (new \App\Common\Models\User)->getTable();
                $org = (new \App\Common\Models\Purchaser)->getTable();
                $q->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('bill_no', 'like', '%' . $keyword . '%')
                        ->orWhereRaw('EXISTS(SELECT o.id FROM ' . $org
                                . ' as o WHERE o.name like \'%' . $keyword . '%\''
//                    . ' OR u.email like \'%' . $keyword . '%\''
//                    . ' OR u.phone like \'%' . $keyword . '%\') '
                                . ' AND o.deleted_flag=\'N\' AND o.id=inquiry.org_id)');
            });
        }
        if (!empty($request->biz_status)) {
            $bizStatus = $request->biz_status;
            $bizStatusies = is_array($bizStatus) ? $bizStatus : explode(',', trim($bizStatus));
            $query->whereIn('biz_status', $bizStatusies);
        }

        if (!empty($request->person_name)) {
            $user = (new \App\Common\Models\User)->getTable();
            $personName = trim($request->person_name);
            $query->WhereRaw('EXISTS(SELECT u.user_id FROM ' . $user
                    . ' as u WHERE u.realname like \'%' . $personName . '%\''
                    . ' AND u.deleted_flag=\'N\' AND u.user_id=inquiry.person_id)');
        }
        if (!empty($request->bill_status)) {
            $billStatus = $request->bill_status;
            $billStatusies = is_array($billStatus) ? $billStatus : explode(',', trim($billStatus));
            $query->whereIn('bill_status', $billStatusies);
        }
        if (!empty($request->bill_no)) {
            $query->where('bill_no', 'like', '%' . trim($request->bill_no) . '%');
        }
        if (!empty($request->related_no)) {
            $query->where('related_no', 'like', '%' . trim($request->related_no) . '%');
        }
        if (!empty($request->sup_scope)) {
            $query->where('sup_scope', trim($request->sup_scope));
        }
        if (!empty($request->title)) {
            $query->where('title', 'like', '%' . trim($request->title) . '%');
        }
        if (!empty($request->open_type)) {
            $query->where('open_type', trim($request->open_type));
        }

        if (!empty($request->statusies)) {
            $query->whereIn('biz_status', $request->statusies);
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

    /**
     * 导出
     * @param $request
     * @return array
     */
    public function export(Request $request) {
        $query = $this->model->selectRaw('id,bill_no,title,biz_status,open_type,delivery_date,'
                . 'tax_cal_type,inv_type,payment_terms,settle_type_id,curr_id,phone,related_no,'
                . 'date_from,date_to,total_inquiry,'
                . 'bill_status,bill_date,end_date,person_id,org_id,turns,sup_scope,remark');
        if ($request->type === 'ALL') {
            $query->where('deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('deleted_flag', 'N')
                    ->whereIn('id', $request->ids);
        } else {
            $this->getWhere($query, $request);
        }
        $clone = $query->clone();
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $count = $clone->count();
        $files = [];
        $name = '询价单_' . date("YmdHis", time());
        $realtive = "/download/" . date("Ymd") . '/';
        $filedir = base_path() . '/public' . $realtive;
        @mkdir($filedir, 0777, true);
        for ($i = 0; $i < $count; $i += 250) {
            $files[] = $this->getExportList($query, $name, $filedir, $i);
        }
        $zip = new ZipArchive();
        $zipFile = $name . '.zip';
        $filepath = $filedir . '/' . $zipFile;
        $res = $zip->open($filepath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
        if ($res !== true) {
            return false;
        }
        foreach ($files as $item) {
            if ($item != '.' && $item != '..') {
                $zip->addFile($item['file_url'], $item['attach_name']);
            }
        }
        $zip->close();
        $url = env('APP_URL') . $realtive . $zipFile;
        return ['file_url' => $url, 'attach_name' => $zipFile];
    }

    public function getExportList($query, $name, $filedir, $i) {
        $query->orderBy('bill_date', 'DESC');
        $query->offset($i)->limit(250);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            $item['biz_status_name'] = $this->getBizStatusText($item['biz_status']);
            $item['sup_scope_name'] = $this->getSupScopeText($item['sup_scope']);
            $item['open_type_name'] = $this->getOpenTypeText($item['open_type']);
            $item['tax_cal_type_name'] = $this->getTaxCalTypeText($item['tax_cal_type']);
            $item['turns_name'] = $this->getTurnsText($item['turns']);
            $item['inv_type_name'] = $this->getInvtypeText($item['inv_type']);
        }
        (new CurrencyRepo)->setCurrencys($data, 'curr_id', ['curr_name' => 'name']);
        (new PaycondRepo)->setPayconds($data, 'payment_terms');
        (new SettleMentTypeRepo)->setSettleMentTypes($data, 'settle_type_id', 'settle_type_name');
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        (new EntryRepo)->setEntrys($data);
        $maxSupplier = 0;
        (new SupplierRepo)->setSuppliers($data, $maxSupplier);
        $headName = $this->getHeadName();
        $xlsName = $name . '_' . round($i / 250 + 1, 0); //文件名称

        return $this->downloadExcel($xlsName, $filedir, $data, $headName, $maxSupplier);
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
     * @param type $name Description
     * @param $data
     * @param array $head
     * @param array $maxSupplier
     * @return array
     */
    public function downloadExcel($name, $filedir, $data = [], $head = [], $maxSupplier = 0) {
        $count = count($head);  //计算表头数量
        $spreadsheet = Excel::newSpreadsheet();
        $styleArray = $this->styleArray;
        $sheet = $spreadsheet->getSpreadsheet()->getActiveSheet();
        for ($i = 1; $i <= $count + $maxSupplier; $i++) {     //数字转字母从65开始
            $column = Excel::num2alpha($i);
            if ($i <= $count) {
                $this->setExcelRow($sheet, $column, 1, $head[$i - 1], 20);
            } else {
                $this->setExcelRow($sheet, $column, 1, '供应商' . ($i - $count ), 20);
            }
        }
        $row = 2;
        foreach ($data as $item) {
            if (empty($item['entrys'])) {
                $this->setQuoteExcelRow($item, $sheet, $row, $styleArray);
                $row++;
                continue;
            }
            foreach ($item['entrys'] as $entry) {
                $this->setInquiryExcelRow($item, $sheet, $row, $styleArray);
                $this->setExcelRow($sheet, 'X', $row, $entry['material_name'], 24);
                $sheet->getStyle('X' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'Y', $row, $entry['material_desc'], 24);
                $sheet->getStyle('Y' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'Z', $row, $entry['inquire_qty'], 24);
                $sheet->getStyle('Z' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'AA', $row, $entry['inquiry_unit_id_name'], 24);
                $sheet->getStyle('AA' . $row)->applyFromArray($styleArray);
                $row++;
                continue;
            }
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:G2')
                ->applyFromArray($styleArray);
        $filename = $name . '.xlsx';
        $filepath = $filedir . $filename;
        $spreadsheet->save($filepath);
        $url = $filedir . $filename;
        return ['file_url' => $url, 'attach_name' => $filename];
    }

    public function setInquiryExcelRow($item, $sheet, $row, $styleArray) {
        $this->setExcelRow($sheet, 'A', $row, ' ' . $item['bill_no'], 17);
        $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'B', $row, $item['title'], 24);
        $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'C', $row, $item['bill_status_name'], 24);
        $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'D', $row, $item['biz_status_name'], 24);
        $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'F', $row, $item['bill_date'], 24);
        $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'G', $row, $item['end_date'], 24);
        $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'H', $row, $item['turns'], 24);
        $sheet->getStyle('H' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'I', $row, $item['sup_scope_name'], 24);
        $sheet->getStyle('I' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'J', $row, $item['open_type_name'], 24);
        $sheet->getStyle('J' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'K', $row, $item['person_name'], 24);
        $sheet->getStyle('K' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'L', $row, $item['phone'], 24);
        $sheet->getStyle('L' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'M', $row, $item['related_no'], 24);
        $sheet->getStyle('M' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'N', $row, $item['total_inquiry'] == '1' ? '是' : '否', 24);
        $sheet->getStyle('N' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'O', $row, $item['remark'], 24);
        $sheet->getStyle('O' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'P', $row, $item['delivery_date'], 24);
        $sheet->getStyle('P' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'Q', $row, $item['date_from'], 24);
        $sheet->getStyle('Q' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'R', $row, $item['date_to'], 24);
        $sheet->getStyle('R' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'S', $row, $item['settle_type_name'], 24);
        $sheet->getStyle('S' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'T', $row, $item['paycond_name'], 24);
        $sheet->getStyle('T' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'U', $row, $item['tax_cal_type_name'], 24);
        $sheet->getStyle('U' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'V', $row, $item['curr_name'], 24);
        $sheet->getStyle('V' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'W', $row, $item['inv_type_name'], 24);
        $sheet->getStyle('W' . $row)->applyFromArray($styleArray);

        if (empty($item['suppliers'])) {
            return;
        }

        foreach ($item['suppliers'] as $key => $supplier) {
            $column = Excel::num2alpha(28 + $key);
            $this->setExcelRow($sheet, $column, $row, $supplier['supplier_name'], 24);
            $sheet->getStyle($column . $row)->applyFromArray($styleArray);
        }
    }

    /**
     * 获取headName
     * @param $data
     * @return array
     */
    public function getHeadName() {
        return [
            '询价单号',
            '询价标题',
            '单据状态',
            '项目状态',
            '采购组织',
            '业务日期',
            '报价截止日期',
            '轮次',
            '询价范围',
            '开标方式',
            '采购员',
            '联系电话',
            '关联单号',
            '整单询价',
            '备注',
            '交货期日期',
            '价格有效期从',
            '价格有效期至',
            '结算方式',
            '付款条件',
            '计税类型',
            '币种',
            '发票类型',
            '物料名称',
            '物料描述',
            '询价数量',
            '询价单位',
        ];
    }

    /**
     * @desc 处理业务SKU参数
     *
     * @param array $importData 规格属性
     * @return bool
     * @author zhongyg
     * @time 2019-06-14
     */
    public function importItemHandler($importData) {
        array_shift($importData); //去掉第二行数据(excel文件的标题)
        array_shift($importData);
        if (empty($importData)) {
            return ['code' => '-104', 'message' => '没有要导入的数据'];
        }
        $admin = Auth::guard('admin')->user();
        $data = $this->dataTrim($importData);
        return $data;
    }

    /**
     * @desc 去掉数据两侧的空格
     *
     * @param mixed $data
     * @return mixed
     * @author liujf
     * @time 2018-02-02
     */
    function dataTrim($data) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->dataTrim($v);
            }
            return $data;
        }
        if (is_object($data)) {
            foreach ($data as $k => $v) {
                $data->$k = $this->dataTrim($v);
            }
            return $data;
        }
        if (is_string($data)) {
            return trim($data);
        }
        return $data;
    }

    /**
     * 远程文件现在到本地临时目录处理完毕后自动删除)
     * @param $remoteFile 远程文件地址
     *
     * @return string 本地的临时地址
     */
    public function download2local($tmpSavePath, $remoteFile, $attach_name) {
//设置本地临时保存目录
        $localFullFileName = $tmpSavePath . mb_convert_encoding(urldecode(basename($attach_name)), 'GB2312', 'UTF-8');
        $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $file = fopen($remoteFile, 'rb', null, $context);
        if ($file) {
            $newf = fopen($localFullFileName, 'wb');
            if ($newf) {
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        return $localFullFileName;
    }

    public function import(Request $request) {
        $remoteFile = $request->file_url;
        $attachName = $request->attach_name;
        $ds = DIRECTORY_SEPARATOR;
        $tmpDir = app()->basePath() . $ds . 'resources' . $ds . 'tmp' . $ds . uniqid() . $ds;
        RecursiveMkdir($tmpDir);
        $localFile = $this->download2local($tmpDir, $remoteFile, $attachName);
        $importData = $this->ready2import($localFile, 0);
        return $this->importItemHandler($importData);
    }

    public function ready2import($localFile, $pIndex = 0) {
//获取文件类型
        $fileType = IOFactory::identify($localFile);
//创建PHPExcel读取对象
        $objReader = IOFactory::createReader($fileType);
//加载文件并读取
        $officeSheet = $objReader->load($localFile);
        $data = $officeSheet->getSheet($pIndex)->toArray();
        return $data;
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getInquiryNo($newNumber = null) {
        $prefix = 'XJ';
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

    public function getTaxCalTypeList() {
        return [
            '1' => '价外税(含税)',
            '2' => '价外税(不含税)',
            '3' => '价内税(含税)',
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
     * 变更项目截止时间
     * @param Request $request
     */
    public function changeEndDate(Request $request) {
        $id = $request->id;
        $inquiry = Inquiry::where('id', $id)->first();
        $orgId = $this->getPPurchaserId();
        if (empty($inquiry)) {
            check(false, '询价不存在');
        }


        $flag = Inquiry::where('id', $id)
                ->update(['end_date' => $request->end_date,
            'biz_status' => 'A'
        ]);
        InquirySupplier::where('inquiry_id', $id)
                ->whereIn('supplier_biz_status', ['A', 'D'])
                ->update([
                    'supplier_biz_status' => 'A',
                    'dead_line' => $request->end_date,
        ]);
        $data = $this->notice($id);
        $nrequest = (new Request);
        $nrequest->merge($data);
        $noticeId = NoticeSub::where('src_bill_id', $id)
                ->where('src_bill_type', 'sou_inquiry')
                ->value('notice_id');
        if (empty($noticeId)) {
            return $flag;
        }
        (new NoticeManageRepo)->edited($noticeId, $nrequest);
        return $flag;
    }

    /**
     * 撤销
     * @param int $id
     */
    public function revoke(int $id) {
        $count = Quote::where('bill_status', 'C')
                ->where('inquiry_id', $id)
                ->where('deleted_flag', 'N')
                ->count();
        check($count === 0, '已有供应商报价,不能撤销');
        $inquiry = Inquiry::where('id', $id)
                ->where('deleted_flag', 'N')
                ->first();
        check($inquiry->biz_status === 'A', '询价状态不是报价中,不能撤销');
        return Inquiry::where('id', $id)->update(['bill_status' => 'A', 'biz_status' => null]);
    }

    /**
     * 终止
     * @param Request $request
     */
    public function stop(Request $request) {
        $admin = Auth::guard('admin')->user();
        check(!empty($request->id), '请选择需要终止的询单');
        $inquiry = Inquiry::lockForUpdate()
                ->select('id', 'open_type', 'bill_no', 'org_id', 'biz_status', 'bill_status')
                ->where('deleted_flag', 'N')
                ->where('id', $request->id)
                ->first();
        check(!empty($inquiry), '询价单不存在');
        check($inquiry->bill_status === 'C', '询价单不是已审核');
        check(in_array($inquiry->biz_status, ['A', 'B']), '询价单不是报价中或开标中');
        $compareStatus = compare::lockForUpdate()
                ->select('bill_status')
                ->where('inquiry_id', $request->id)
                ->where('deleted_flag', 'N')
                ->value('bill_status');
        if ($compareStatus === 'B') {
            check(false, '比价单审批中，不允许终止');
        } elseif ($compareStatus === 'C') {
            check(false, '比价单已审批，不允许终止');
        }

        $flag = Inquiry::where('id', $request->id)
                ->update(['biz_status' => 'E',
            'stopped_reason' => $request->reason,
            'stopped_by' => $admin->user_id,
            'stopped_at' => date('Y-m-d H:i:s'),
        ]);
        InquirySupplier::where('inquiry_id', $request->id)
                ->update(['supplier_biz_status' => 'E',
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $userSupplier = (new UserSupplier)->getTable();
        $supplier = (new InquirySupplier)->getTable();
        $bossUrl = env('BOSS_URL');

        $supplierObj = InquirySupplier::from($supplier . ' as s')
                ->select('s.supplier_id', 'us.user_id', 'quote_id')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('s.supplier_id', 'us.supplier_id');
                })
                ->where('s.deleted_flag', 'N')
                ->where('us.deleted_flag', 'N')
                ->groupBy('s.supplier_id')
                ->groupBy('us.user_id')
                ->get();
        if (empty($supplierObj)) {
            Inquiry::sharedLock();
            return $flag;
        }
        $supplierList = $supplierObj->toArray();
        $dataList = [];
        foreach ($supplierList as $supplier) {
            if ($supplier['quote_id']) {
                $messageId = Message::insertGetId([
                            'receiver_type' => 'SUPPLIER',
                            'content_url' => $bossUrl . '/front/#/quoteManage/quoteAssistant?id=' . $supplier['quote_id'] . '&type=info',
                            'sender_id' => $inquiry->org_id,
                            'message_type' => 'SYSTEM',
                            'message_title' => '询价终止通知',
                            'message' => '你有询价单' . $inquiry->bill_no . '已由采购方终止，请知悉。',
                            'created_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $messageId = Message::insertGetId([
                            'receiver_type' => 'SUPPLIER',
                            'content_url' => $bossUrl . '/front/#/quoteManage/inquiryDetail?id=' . $inquiry->id . '&type=info',
                            'sender_id' => $inquiry->org_id,
                            'message_type' => 'SYSTEM',
                            'message_title' => '询价终止通知',
                            'message' => '你有询价单' . $inquiry->bill_no . '已由采购方终止，请知悉。',
                            'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $dataList[] = [
                'message_id' => $messageId,
                'receiver_id' => $supplier['user_id'],
                'supplier_id' => $supplier['supplier_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (empty($dataList)) {
            Inquiry::sharedLock();
            return $flag;
        }
        MessageReceiver::insert($dataList);
        Inquiry::sharedLock();
        return $flag;
    }

    public function audit(Request $request) {
        $admin = Auth::guard('admin')->user();
        $inquiryId = $request->post('id');
        $status = $request->post('status');
        $remark = $request->post('remark');
        $audit = Inquiry::lockForUpdate()
                ->select('id', 'open_type', 'bill_no', 'org_id')
                ->where('deleted_flag', 'N')
                ->where('id', $inquiryId)
                ->where('bill_status', 'B')
                ->first();
        check(!empty($audit), '空数据');
        if ($status !== 'PASS') {
            $flag = Inquiry::where('id', $inquiryId)
                    ->where('bill_status', 'B')
                    ->update(['bill_status' => 'A']);
            Inquiry::sharedLock();
            return $flag;
        }
        $flag = Inquiry::where('id', $inquiryId)
                ->where('bill_status', 'B')
                ->update(['bill_status' => 'C', 'biz_status' => 'A']);
        Sub::where('inquiry_id', $inquiryId)->update([
            'audit_date' => date('Y-m-d H:i:s'),
            'auditor_id' => $admin->user_id,
            'audit_remark' => $remark
        ]);
        $userSupplier = (new UserSupplier)->getTable();
        $supplier = (new InquirySupplier)->getTable();
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/quoteManage/inquiryDetail?id=' . $inquiryId . '&type=info',
                    'sender_id' => $audit->org_id,
                    'message_type' => 'SYSTEM',
                    'message_title' => '询价单已生效，请及时报价！',
                    'message' => '你有询价单' . $audit->bill_no . '待报价，请处理！',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $supplierObj = InquirySupplier::from($supplier . ' as s')
                ->select('s.supplier_id', 'us.user_id')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('s.supplier_id', 'us.supplier_id');
                })
                ->where('s.inquiry_id', $inquiryId)
                ->where('s.deleted_flag', 'N')
                ->where('us.deleted_flag', 'N')
                ->groupBy('s.supplier_id')
                ->groupBy('us.user_id')
                ->get();
        if (empty($supplierObj)) {
            Inquiry::sharedLock();
            return $flag;
        }
        $supplierList = $supplierObj->toArray();
        $dataList = [];
        foreach ($supplierList as $supplier) {
            $dataList[] = [
                'message_id' => $messageId,
                'receiver_id' => $supplier['user_id'],
                'supplier_id' => $supplier['supplier_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (empty($dataList)) {
            Inquiry::sharedLock();
            return $flag;
        }
        MessageReceiver::insert($dataList);
        Inquiry::sharedLock();
        return $flag;
    }

    /**
     * 开标
     * @param int $id
     */
    public function opening(int $id) {
        return Inquiry::where('id', $id)
                        ->where('open_type', 3)
                        ->where('bill_status', 'C')
                        ->where('biz_status', 'A')
                        ->update(['biz_status' => 'B']);
    }

    /**
     * 变更项目截止时间
     * @param int $id
     */
    public function copy(int $id) {
        $inquirObj = Inquiry::where('id', $id)->first();
        $entryObj = \App\Common\Models\Inquiry\Entry::where('inquiry_id', $id)->get();
        if (empty($inquirObj)) {
            check(false, '询单不存在');
        }
        $admin = Auth::guard('admin')->user();
        $inquirData = $inquirObj->toArray();
        unset($inquirData['id'], $inquirData['biz_status'], $inquirData['updated_by'], $inquirData['updated_at']);
        $inquirData['created_at'] = date('Y-m-d H:i:s');
        $inquirData['created_by'] = $admin->user_id;
        $inquirData['bill_status'] = 'A';
        $inquirData['bill_date'] = date('Y-m-d');
        $inquirData['biz_status'] = null;
        $inquirData['bill_no'] = $this->getInquiryNo();
        $inquirData['turns'] = 1;
        $nrequest = new Request();
        $nrequest->merge(['base' => $inquirData,
            'attachs' => [],
            'suppliers' => [],
            'entrys' => !empty($entryObj) ? $entryObj->toArray() : [],
        ]);
        $inquiryId = Inquiry::insertGetId($inquirData);
        $inquirData[] = [];
        (new EntryRepo())->updateData($inquiryId, $nrequest);
        $inquirData['id'] = (string) $inquiryId;
        return $inquirData;
    }

    /**
     * 多轮报价
     * @param Request $request
     */
    public function mulRound(Request $request) {
        check(!empty($request->id), '询价ID不能为空');
        check(!empty($request->end_date), '报价截止日期不能为空');
        check(!empty($request->note), '原因说明不能为空');
        check(!empty($request->supplier_ids), '供应商ID不能为空');
        $id = $request->id;
        $endDate = $request->end_date;
        $entryFlag = $request->entry_flag;
        $inquiry = Inquiry::where('id', $id)
                ->where('deleted_flag', 'N')
                ->selectRaw('turns,end_date,bill_status,biz_status,org_id,bill_no,id')
                ->first();
        check(!empty($inquiry), '询价单不存在');
        check($inquiry->bill_status === 'C', '单据状态不是已审核');
        check($inquiry->biz_status === 'B', '项目状态不是已开标');
//        check($request->end_date >= $inquiry->end_date, '多轮报价截止日期应大于原报价截止日期');
        $compareCount = compare::lockForUpdate()
                ->select('bill_status')
                ->where('inquiry_id', $id)
                ->where('deleted_flag', 'N')
                ->count();
        if (!empty($compareCount)) {
            check(false, '存在比价单，请先删除比价单后再开启多轮报价');
        }
        $turns = $inquiry->turns;
        $flag = Inquiry::where('id', $id)
                ->update(['turns' => !empty(trim($turns)) ? trim($turns) + 1 : 2,
            'end_date' => $endDate,
            'biz_status' => 'A'
        ]);
        $newTurns = !empty(trim($turns)) ? trim($turns) + 1 : 2;
        $quoteList = \App\Common\Models\Quote\Quote::selectRaw('count(id) as entry_count,supplier_id')
                ->where('inquiry_id', $id)
                ->where('deleted_flag', 'N')
                ->where('bill_status', 'C')
                ->whereIn('supplier_id', $request->supplier_ids)
                ->groupBy('supplier_id')
                ->get()
                ->toArray();
        $quoteArr = array_column($quoteList, 'entry_count', 'supplier_id');
        $supplierList = \App\Common\Models\SupplierContact::select('supplier_id', 'contact_name', 'phone', 'email')
                ->where('deleted_flag', 'N')
                ->whereIn('supplier_id', $request->supplier_ids)
                ->orderBy('default_flag', 'DESC')
                ->get()
                ->toArray();
        $contactNames = array_column($supplierList, 'contact_name', 'supplier_id');
        $contactPhones = array_column($supplierList, 'phone', 'supplier_id');
        $contactEmails = array_column($supplierList, 'email', 'supplier_id');
        (new TurnsLogRepo)->updateData($request, $turns);
        \App\Common\Models\Inquiry\Supplier::where('inquiry_id', $id)
                ->where('entry_turns', !empty(trim($turns)) ? trim($turns) + 1 : 2)
                ->delete();
        $supplierData = [];
        foreach ($request->supplier_ids as $key => $supplierId) {
            $supplierData[] = [
                'inquiry_id' => $id,
                'seq' => ($key + 1),
                'supplier_id' => $supplierId,
                'entry_status' => '',
                'dead_line' => $request->end_date,
                'supplier_biz_status' => 'A',
                'entry_turns' => !empty(trim($turns)) ? trim($turns) + 1 : 2,
                'entry_count' => !empty($quoteArr[$supplierId]) ? $quoteArr[$supplierId] : 1,
                'contact_name' => !empty($contactNames[$supplierId]) ? $contactNames[$supplierId] : '',
                'contact_phone' => !empty($contactPhones[$supplierId]) ? $contactPhones[$supplierId] : '',
                'contact_email' => !empty($contactEmails[$supplierId]) ? $contactEmails[$supplierId] : '',
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        \App\Common\Models\Inquiry\Supplier::insert($supplierData);
        if ($entryFlag == 'Y') {
            (new EntryRepo)->updateData($id, $request, $newTurns);
        } else {
            Entry::where('inquiry_id', $id)
                    ->whereRaw('FIND_IN_SET(' . $turns . ',turns)')
                    ->update(['turns' => DB::Raw('CONCAT(turns,\',\',\'' . $newTurns . '\')')]);
        }
        $userSupplier = (new UserSupplier)->getTable();
        $supplier = (new InquirySupplier)->getTable();
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $bossUrl . '/front/#/quoteManage/inquiryDetail?id=' . $inquiry->id . '&type=info',
                    'sender_id' => $inquiry->org_id,
                    'message_type' => 'SYSTEM',
                    'message_title' => '多轮报价已生效，请及时报价！',
                    'message' => '您的询价单【' . $inquiry->bill_no . '】待报价，请处理！',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $supplierObj = InquirySupplier::from($supplier . ' as s')
                ->select('s.supplier_id', 'us.user_id')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('s.supplier_id', 'us.supplier_id');
                })
                ->where('s.inquiry_id', $id)
                ->where('entry_turns', !empty(trim($turns)) ? trim($turns) + 1 : 2)
                ->where('s.deleted_flag', 'N')
                ->where('us.deleted_flag', 'N')
                ->groupBy('s.supplier_id')
                ->groupBy('us.user_id')
                ->get();
        if (empty($supplierObj)) {
            return $flag;
        }
        $suppliers = $supplierObj->toArray();
        $dataList = [];
        foreach ($suppliers as $supplier) {
            $dataList[] = [
                'message_id' => $messageId,
                'receiver_id' => $supplier['user_id'],
                'supplier_id' => $supplier['supplier_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (empty($dataList)) {
            return $flag;
        }
        MessageReceiver::insert($dataList);
        return $flag;
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

    public function getInvtypeList() {
        return [
//            '1' => '普通电子发票',
//            '2' => '电子发票专票',
//            '3' => '普通纸质发票',
//            '4' => '专用纸质发票',
//            '5' => '普通纸质卷票',
            '9' => '不需要发票',
            '6' => '增值税专用发票',
            '7' => '增值税普通发票',
        ];
    }

    public function getTurnsText($turns) {
        switch (strtoupper($turns)) {
            case '1':
                return '首轮';
            case '2':
                return '第二轮';
            case '3':
                return '第三轮';
            case '4':
                return '第四轮';
            case '5':
                return '第五轮';
            case '6':
                return '第六轮';
            case '7':
                return '第七轮';
            case '8':
                return '第八轮';
            case '9':
                return '第九轮';
            case '10':
                return '第十轮';
            case '11':
                return '第十一轮';
            case '12':
                return '第十二轮';
            case '13':
                return '第十三轮';
            case '14':
                return '第十四轮';
            case '15':
                return '第十五轮';
            case '16':
                return '第十六轮';
            case '17':
                return '第十七轮';
            case '18':
                return '第十八轮';
            case '19':
                return '第十九轮';
            case '20':
                return '第二十轮';
        }
    }

    /**
     * 报价次数
     */
    public function getTurnsCountText($turns) {
        switch (strtoupper($turns)) {
            case '1':
                return '第一次';
            case '2':
                return '第二次';
            case '3':
                return '第三次';
            case '4':
                return '第四次';
            case '5':
                return '第五次';
            case '6':
                return '第六次';
            case '7':
                return '第七次';
            case '8':
                return '第八次';
            case '9':
                return '第九次';
            case '10':
                return '第十次';
            case '11':
                return '第十一次';
            case '12':
                return '第十二次';
            case '13':
                return '第十三次';
            case '14':
                return '第十四次';
            case '15':
                return '第十五次';
            case '16':
                return '第十六次';
            case '17':
                return '第十七次';
            case '18':
                return '第十八次';
            case '19':
                return '第十九次';
            case '20':
                return '第二十次';
        }
    }

    public function notice($inquiryId) {
        $inquiry = Inquiry::where('id', $inquiryId)->first()->toArray();
        $materialList = Entry::where('deleted_flag', 'N')->where('inquiry_id', $inquiryId)->get()->toArray();
        $token = Auth::guard('admin')->getToken();
        $redisKey = md5($token);
        $curId = '';
        if (!empty($token) && Redis::command('exists', [$redisKey])) {
            $curId = Redis::get($redisKey);
        }
        $obj = Purchaser::where('id', $curId)
                ->where('deleted_flag', 'N')
                ->where('enable', 1)
                ->first();
        (new PaycondRepo)->setPaycond($inquiry, 'payment_terms');
        (new UnitRepo)->setUnits($materialList, 'inquiry_unit_id', 'inquiry_unit_name');
        $user = User::where('user_id', $inquiry['person_id'])->where('deleted_flag', 'N')->first();
        $content = [
            'inquiry_title' => $inquiry['title'],
            'bill_no' => $inquiry['bill_no'],
            'bill_date' => date('Y-m-d', strtotime($inquiry['bill_date'])),
            'deli_date' => !empty($inquiry['deli_date']) ? date('Y-m-d', strtotime($inquiry['deli_date'])) : null,
            'deli_addr' => $inquiry['deli_addr'],
            'due_date' => date('Y-m-d H:i:s', strtotime($inquiry['end_date'])),
            'paycond_name' => $inquiry['paycond_name'],
            'other_pay_terms' => $inquiry['other_pay_terms_info'],
            'person_name' => $user->realname,
            'person_phone' => $user->phone,
            'materials' => $materialList,
        ];
        $data = [
            'biz_type' => 1,
            'due_date' => date('Y-m-d H:i:s', strtotime($inquiry['end_date'])),
            'org_id' => $inquiry['org_id'],
            'bill_no' => (new NoticeManageRepo)->getNoticeNo(),
            'src_bill_id' => $inquiryId,
            'src_bill_type' => 'sou_inquiry',
            'bill_status' => 'C',
            'bill_type_id' => 0,
            'src_bill_no' => '询价单：' . $inquiry['bill_no'],
            'sup_scope' => $inquiry['sup_scope'],
            'org_id' => $curId,
            'title' => $inquiry['title'] . '-询价采购公告',
        ];
        $data['content'] = view('tpl.inquiry_notice', $content)->toHtml();
        return $data;
    }

}
