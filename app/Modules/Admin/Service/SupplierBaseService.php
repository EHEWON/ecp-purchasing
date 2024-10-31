<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Repository\SupplierBaseRepo,
    Middleware\SupplierMiddleware,
    Middleware\SupplierAttachMiddleware,
    Middleware\SupplierBankMiddleware,
    Middleware\SupplierContactMiddleware
};
use Illuminate\Http\Request;

class SupplierBaseService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        SupplierMiddleware::class => ['only' => ['add',
                'edited',
                'enable',
                'disable',
                'delete',
                'register',
                'phoneEmail']],
        SupplierAttachMiddleware::class => ['only' => ['add', 'edited']],
        SupplierBankMiddleware::class => ['only' => ['add', 'edited']],
        SupplierContactMiddleware::class => ['only' => ['add', 'edited']],
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
        return (new SupplierBaseRepo)->getList($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function suppliers(Request $request) {
        return (new SupplierBaseRepo)->suppliers($request);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new SupplierBaseRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new SupplierBaseRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new SupplierBaseRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new SupplierBaseRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new SupplierBaseRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new SupplierBaseRepo)->import($request);
    }

    public function export(Request $request) {
        return (new SupplierBaseRepo)->export($request);
    }

    /**
     * 获取询单状态
     * @return
     */
    public function status() {
        $supplierRepo = new SupplierBaseRepo();
        return $supplierRepo->getStatusList();
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function registerList(Request $request) {
        return (new SupplierBaseRepo)->registerList($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function supplierList(Request $request) {
        return (new SupplierBaseRepo)->supplierList($request);
    }

    /**
     * 人员类型列表
     * @param int $id
     */
    public function auditInfo(int $id, Request $request) {
        return (new SupplierBaseRepo)->auditInfo($id, $request);
    }

    /**
     * 人员类型列表
     * @param int $id
     */
    public function editInfo(int $id, Request $request) {
        return (new SupplierBaseRepo)->info($id, $request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id, Request $request) {
        return (new SupplierBaseRepo)->info($id, $request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function formal(Request $request) {
        return (new SupplierBaseRepo)->formal($request);
    }

}
