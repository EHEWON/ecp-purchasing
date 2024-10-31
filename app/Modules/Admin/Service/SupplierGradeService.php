<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Repository\SupplierGradeRepo,
    Middleware\SupplierGradeMiddleware,
    Middleware\SupplierGradentryMiddleware
};
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class SupplierGradeService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        SupplierGradeMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
        SupplierGradentryMiddleware::class => ['only' => ['add', 'edited']],
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
        return (new SupplierGradeRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new SupplierGradeRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new SupplierGradeRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new SupplierGradeRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new SupplierGradeRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new SupplierGradeRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new SupplierGradeRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new SupplierGradeRepo)->import($request);
    }

    public function export(Request $request) {
        return (new SupplierGradeRepo)->export($request);
    }

    public function number() {
        $newNumber = null;
        $redisKey = 'ECP:SUPPLIER_GRADE:NUMBER';
        if (Redis::command('exists', [$redisKey])) {
            $newNumber = Redis::get($redisKey);
        }
        $number = (new SupplierGradeRepo)->getSupplierGradeNo($newNumber);
        Redis::set($redisKey, $number, 86400);
        return $number;
    }

}
