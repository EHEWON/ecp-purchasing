<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Repository\Inquiry\InquiryRepo,
    Repository\Inquiry\InquiryExportRepo,
    Repository\Inquiry\EntryRepo,
    Repository\Inquiry\SupplierRepo,
    Middleware\InquiryMiddleware,
    Middleware\InquirySupplierMiddleware,
    Middleware\InquiryEntryMiddleware
};
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class InquiryService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        InquiryMiddleware::class => ['only' => ['add', 'edited', 'change']],
        InquiryEntryMiddleware::class => ['only' => ['add', 'edited']],
        InquirySupplierMiddleware::class => ['only' => ['add', 'edited']],
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
        return (new InquiryRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new InquiryRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new InquiryRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new InquiryRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new InquiryRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new InquiryRepo)->disable($request);
    }

    /**
     * 询价审核
     * @return
     */
    public function audit(Request $request) {
        return (new InquiryRepo)->audit($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new InquiryRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new InquiryRepo)->import($request);
    }

    public function export(Request $request) {
        return (new InquiryExportRepo)->export($request);
    }

    public function number() {
        $newNumber = null;
        $redisKey = 'ECP:INQUIRY:NUMBER:' . date('Ymd');
        if (Redis::command('exists', [$redisKey])) {
            $newNumber = Redis::get($redisKey);
        }
        $number = (new InquiryRepo)->getInquiryNo($newNumber);
        Redis::set($redisKey, $number, 86400);
        return $number;
    }

    public function copy(int $inquiryId) {
        return (new InquiryRepo)->copy($inquiryId);
    }

    public function change(Request $request) {
        return (new InquiryRepo)->changeEndDate($request);
    }

    public function revoke(int $inquiryId) {
        return (new InquiryRepo)->revoke($inquiryId);
    }

    public function stop(Request $request) {
        return (new InquiryRepo)->stop($request);
    }

    public function mulRound(Request $request) {
        return (new InquiryRepo)->mulRound($request);
    }

    public function openType() {
        return (new InquiryRepo)->getOpenTypeList();
    }

    public function billStatus() {
        return (new InquiryRepo)->getBillStatusList();
    }

    public function supScope() {
        return (new InquiryRepo)->getSupScopeList();
    }

    public function taxCalType() {
        return (new InquiryRepo)->getTaxCalTypeList();
    }

    public function bizStatus() {
        return (new InquiryRepo)->getBizStatusList();
    }

    public function opening($id) {
        return (new InquiryRepo)->opening($id);
    }

    public function notice($id) {
        return (new InquiryRepo)->notice($id);
    }

    public function entryExport($id) {
        return (new EntryRepo)->export($id);
    }

    public function entryImport(Request $request) {
        return (new EntryRepo)->import($request);
    }

    public function invType() {
        return (new InquiryRepo)->getInvtypeList();
    }

    public function entryTemplate() {
        return env('INQUIRY_GOODS_TEMP');
    }

    public function supplier($id) {
        return (new SupplierRepo)->getList($id);
    }

    public function mulquote($id) {
        $data = [];
        $turns = \App\Common\Models\Inquiry\Inquiry::where('id', $id)->value('turns');
        $data['suppliers'] = (new SupplierRepo)->getList($id);
        $data['entrys'] = (new EntryRepo)->getList($id, $turns);
        return $data;
    }

}
