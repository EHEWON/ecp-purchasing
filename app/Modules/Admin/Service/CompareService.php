<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\Compare\{
    CompareRepo,
    CompareAuditRepo,
    CompareExportRepo
};
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class CompareService extends Service {

    protected $guard = 'admin';
    public $middleware = [
//        UserTypeMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
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
        return (new CompareRepo)->getList($request);
    }

    public function getListGroupBySupplier(Request $request) {
        return (new CompareRepo)->getListGroupBySupplier($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new CompareRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new CompareRepo)->edited($request->id, $request);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function verify(Request $request) {
        return (new CompareAuditRepo)->auditStart($request->id, $request);
    }

    public function stop(Request $request) {
        return (new CompareAuditRepo)->stop($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new CompareRepo)->add($request);
    }

    /**
     * 删除
     * @return
     */
    public function delete(Request $request) {
        return (new CompareRepo)->deleteData($request);
    }

    public function export(Request $request) {
        return (new CompareExportRepo)->export($request);
    }

    public function number() {
        $newNumber = null;
        $redisKey = 'ECP:COMPARE:NUMBER:' . date('Ymd');
        if (Redis::command('exists', [$redisKey])) {
            $newNumber = Redis::get($redisKey);
        }
        $number = (new CompareRepo)->getCompareNo($newNumber);
        Redis::set($redisKey, $number, 86400);
        return $number;
    }

    /**
     * 删除
     * @return
     */
    public function notice(Request $request) {
        return (new CompareRepo)->notice($request->compare_id, $request->inquiry_id);
    }

}
