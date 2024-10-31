<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Repository\PurchaserRepo,
    Middleware\PurchaserMiddleware
};
use Illuminate\Http\Request;

class PurchaserService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        PurchaserMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
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
        return (new PurchaserRepo)->getList($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function getAll(Request $request) {
        return (new PurchaserRepo)->getAll($request);
    }

    public function tree(Request $request) {
        return (new PurchaserRepo)->tree($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new PurchaserRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new PurchaserRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new PurchaserRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new PurchaserRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new PurchaserRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new PurchaserRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new PurchaserRepo)->import($request);
    }

    public function export(Request $request) {
        return (new PurchaserRepo)->export($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function register(Request $request) {
        return (new PurchaserRepo)->register($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function phoneEmail(Request $request) {
        return (new PurchaserRepo)->phoneEmail($request);
    }

}
