<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\DivisionRepo;
use App\Modules\Admin\Middleware\DivisionMiddleware;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class DivisionService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        DivisionMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
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
        return (new DivisionRepo)->getList($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function tree(Request $request) {
        return (new DivisionRepo)->tree($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function china(Request $request) {
        return (new DivisionRepo)->china($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new DivisionRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new DivisionRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new DivisionRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new DivisionRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new DivisionRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new DivisionRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new DivisionRepo)->import($request);
    }

    public function export(Request $request) {
        return (new DivisionRepo)->export($request);
    }

    public function number() {
        $newNumber = null;
        $redisKey = 'ECP:UERTYPE:DIVISION:' . date('Ymd');
        if (Redis::command('exists', [$redisKey])) {
            $newNumber = Redis::get($redisKey);
        }
        $number = (new DivisionRepo)->getDivisionNo($newNumber);
        Redis::set($redisKey, $number, 86400);
        return $number;
    }

}
