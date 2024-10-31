<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\NoticeManageRepo;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class NoticeManageService extends Service {

    protected $guard = 'admin';
    public $middleware = [];
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
        return (new NoticeManageRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new NoticeManageRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new NoticeManageRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new NoticeManageRepo)->addData($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function topping(Request $request) {
        return (new NoticeManageRepo)->topping($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function cancel(Request $request) {
        return (new NoticeManageRepo)->cancel($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new NoticeManageRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new NoticeManageRepo)->import($request);
    }

    public function number() {
        $newNumber = null;
        $redisKey = 'ECP:NOTICE:NUMBER:' . date('Ymd');
        if (Redis::command('exists', [$redisKey])) {
            $newNumber = Redis::get($redisKey);
        }
        $number = (new NoticeManageRepo)->getNoticeManageNo($newNumber);
        Redis::set($redisKey, $number, 86400);
        return $number;
    }

    public function audit(Request $request) {
        return (new NoticeManageRepo)->audit($request);
    }

}
