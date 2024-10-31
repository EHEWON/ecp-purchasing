<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Repository\UnitRepo,
    Middleware\UnitMiddleware
};
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class UnitService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        UnitMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
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
        return (new UnitRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new UnitRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new UnitRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new UnitRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new UnitRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new UnitRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new UnitRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new UnitRepo)->import($request);
    }

    public function export(Request $request) {
        return (new UnitRepo)->export($request);
    }

    public function number() {
        $newNumber = null;
        $redisKey = 'ECP:UERTYPE:UNIT:' . date('Ymd');
        if (Redis::command('exists', [$redisKey])) {
            $newNumber = Redis::get($redisKey);
        }
        $number = (new UnitRepo)->getUnitNo($newNumber);
        Redis::set($redisKey, $number, 86400);
        return $number;
    }

}
