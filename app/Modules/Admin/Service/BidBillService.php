<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Common\Models\BidBill\{
    BidBill,
    BidBillSupplier
};
use App\Modules\Admin\Middleware\{
    BidBillEntryMiddleware,
    BidBillMiddleware,
    BidBillSupplierMiddleware
};
use App\Modules\Admin\Repository\BidBill\{
    BidBillHallRepo,
    BidBillPayRepo,
    BidBillRepo,
    EntryRepo,
    ExportRepo,
    SupplierRepo,
    BidBillDecisionRepo
};
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class BidBillService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        BidBillMiddleware::class => ['only' => ['add', 'edited']],
        BidBillEntryMiddleware::class => ['only' => ['add', 'edited']],
        BidBillSupplierMiddleware::class => ['only' => ['add', 'edited']],
    ];
    public $beforeEvent = [];
    public $afterEvent = [
    ];

    public function getRules() {
        return [
        ];
    }

    public function getMessages() {
        return [
        ];
    }

    protected $model;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function getList(Request $request) {
        return (new BidBillRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new BidBillRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new BidBillRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new BidBillRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new BidBillRepo)->deleteData($request);
    }

    public function change(Request $request) {
        return (new BidBillRepo)->changeEnrollDate($request);
    }

    public function export(Request $request) {
        return (new ExportRepo)->export($request);
    }

    public function number() {
        $newNumber = null;
        $redisKey = 'ECP:BIDBILL:NUMBER:' . date('Ymd');
        if (Redis::command('exists', [$redisKey])) {
            $newNumber = Redis::get($redisKey);
        }
        $number = (new BidBillRepo)->getBidBillNo($newNumber);
        Redis::set($redisKey, $number, 86400);
        return $number;
    }

    public function hall(Request $request) {
        return (new BidBillHallRepo)->getList($request);
    }

    public function decision_list(Request $request) {
        return (new BidBillDecisionRepo)->getList($request);
    }

    public function check(Request $request) {
        return (new BidBillRepo)->check($request->id, $request);
    }

    public function pay(Request $request) {
        return (new BidBillRepo)->pay($request->id, $request);
    }

    public function returns(int $id) {
        return (new BidBillRepo)->returns($id);
    }

    public function returnDeposit(Request $request) {
        return (new BidBillRepo)->returns($request->id, $request);
    }

    public function start(Request $request) {
        return (new BidBillRepo)->start($request->id);
    }

    public function suppliers(int $id) {
        $bidBill = BidBill::where('id', $id)
                        ->select('enroll_date', 'bid_number', 'bid_status', 'deposit_flag', 'cash_deposit', 'name', 'bill_no', 'org_id', 'id', 'person_id'
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
        return (new SupplierRepo)->getList($id, null, 'A');
    }

    public function pays(int $id, $request) {
        $request->merge(['entry_status' => 'L,WQR,O,D']);
        return (new BidBillPayRepo)->getList($id, $request);
    }

    public function winning(int $id) {
        return (new BidBillRepo)->winning($id);
    }

    public function stop(Request $request) {
        return (new BidBillRepo)->stop($request->id, $request);
    }

    public function begin(Request $request) {
        return (new BidBillRepo)->begin($request->id);
    }

    public function decision(Request $request) {
        return (new BidBillRepo)->decision($request->id, $request);
    }

    public function termination(Request $request) {
        return (new BidBillRepo)->termination($request->id, $request);
    }

    public function finished(Request $request) {
        return (new BidBillRepo)->finished($request->id, $request);
    }

    public function hallInfo(int $id) {
        return (new BidBillHallRepo)->hallInfo($id);
    }

    public function bindUid(int $id, Request $request) {
        return (new BidBillHallRepo)->bindGroup($id, $request);
    }

    public function bindGroup(int $id, Request $request) {
        return (new BidBillHallRepo)->bindGroup($id, $request);
    }

    public function offline(int $id, Request $request) {
        return (new BidBillHallRepo)->offline($id, $request);
    }

    public function entryExport(int $id) {
        return (new EntryRepo)->export($id);
    }

    public function entryImport(Request $request) {
        return (new EntryRepo)->import($request);
    }

    public function entryTemplate() {
        return env('BIDBILL_GOODS_TEMP');
    }

}
