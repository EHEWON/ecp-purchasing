<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Repository\UserTypeRepo,
    Middleware\UserTypeMiddleware
};
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class UserTypeService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        UserTypeMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
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
        return (new UserTypeRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new UserTypeRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new UserTypeRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new UserTypeRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new UserTypeRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new UserTypeRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new UserTypeRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new UserTypeRepo)->import($request);
    }

    public function export(Request $request) {
        return (new UserTypeRepo)->export($request);
    }

    public function number() {
        $newNumber = null;
        $redisKey = 'ECP:UERTYPE:NUMBER:' . date('Ymd');
        if (Redis::command('exists', [$redisKey])) {
            $newNumber = Redis::get($redisKey);
        }
        $number = (new UserTypeRepo)->getUserTypeNo($newNumber);
        Redis::set($redisKey, $number, 86400);
        return $number;
    }

}
