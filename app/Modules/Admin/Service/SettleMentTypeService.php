<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Middleware\SettleMentTypeMiddleware,
    Repository\SettleMentTypeRepo
};
use Illuminate\Http\Request;

class SettleMentTypeService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        SettleMentTypeMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
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
        return (new SettleMentTypeRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new SettleMentTypeRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new SettleMentTypeRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new SettleMentTypeRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new SettleMentTypeRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new SettleMentTypeRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new SettleMentTypeRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new SettleMentTypeRepo)->import($request);
    }

    public function export(Request $request) {
        return (new SettleMentTypeRepo)->export($request);
    }

}
