<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Repository\PaycondRepo,
    Middleware\PaycondMiddleware
};
use Illuminate\Http\Request;

class PaycondService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        PaycondMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
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
        return (new PaycondRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new PaycondRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new PaycondRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new PaycondRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new PaycondRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new PaycondRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new PaycondRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new PaycondRepo)->import($request);
    }

    public function export(Request $request) {
        return (new PaycondRepo)->export($request);
    }

    public function number() {
        return (new PaycondRepo)->getPaycondNo();
    }

}
