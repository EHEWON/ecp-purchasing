<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\Project\{
    ExportRepo,
    ProjectBidQuoteRepo,
    ProjectDecisionRepo,
    ProjectEntryRepo,
    ProjectMemberRepo,
    ProjectRepo,
    ProjectSupplierRepo,
    ProjectBidAttachRepo
};
use App\Modules\Admin\Middleware\Project\{
    ProjectMiddleware,
    ProjectEntryMiddleware,
    ProjectSupplierMiddleware,
    ProjectMemberMiddleware
};
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class ProjectService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        ProjectMiddleware::class => ['only' => ['add', 'edited',]],
        ProjectEntryMiddleware::class => ['only' => ['add', 'edited',]],
        ProjectSupplierMiddleware::class => ['only' => ['add', 'edited',]],
        ProjectMemberMiddleware::class => ['only' => ['add', 'edited',]],
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
        return (new ProjectRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new ProjectRepo)->info($id);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function quoteInfo($id) {
        return (new ProjectBidQuoteRepo)->info($id);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function invalidInfo($id) {
        return (new ProjectRepo)->invalidInfo($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new ProjectRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new ProjectRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new ProjectRepo)->deleteData($request);
    }

    /**
     * 招标作废
     * @return
     */
    public function invalid(Request $request) {
        return (new ProjectRepo)->invalid($request);
    }

    public function number() {
        $newNumber = null;
        $redisKey = 'ECP:PROJECT:NUMBER:' . date('Ymd');
        if (Redis::command('exists', [$redisKey])) {
            $newNumber = Redis::get($redisKey);
        }
        $number = (new ProjectRepo)->getProjectNo($newNumber);
        Redis::set($redisKey, $number, 86400);
        return $number;
    }

    public function export(Request $request) {
        return (new ExportRepo)->export($request);
    }

    public function entryImport(Request $request) {
        return (new ProjectEntryRepo)->import($request);
    }

    public function entryTemplate() {
        return env('PROJECT_GOODS_TEMP');
    }

    public function change(Request $request) {
        return (new ProjectRepo)->changeEnrollDate($request);
    }

    public function suppliers(Request $request) {
        return (new ProjectSupplierRepo)->suppliers($request);
    }

    public function members(Request $request) {
        return (new ProjectMemberRepo)->members($request);
    }

    public function shortlist($id, Request $request) {
        return (new ProjectSupplierRepo)->shortlist($id, $request);
    }

    public function shortlistaddData($id, Request $request) {
        return (new ProjectSupplierRepo)->addData($id, $request);
    }

    public function decision(Request $request) {
        return (new ProjectDecisionRepo)->getList($request);
    }

    public function supplierList(Request $request) {
        return (new ProjectSupplierRepo)->supplierList($request);
    }

    public function download(int $quoteId, string $group) {
        return (new ProjectBidAttachRepo)->download($quoteId, $group);
    }

}
