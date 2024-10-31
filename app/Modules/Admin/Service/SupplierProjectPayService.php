<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\Supplier\ProjectPayRepo;
use Illuminate\Http\Request;

class SupplierProjectPayService extends Service {

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
     * 缴费列表
     * @param Request $request
     */
    public function getList(Request $request) {
        return (new ProjectPayRepo)->getList($request);
    }

    /**
     * 缴费信息
     * @return
     */
    public function info($id) {
        return (new ProjectPayRepo)->info($id);
    }

    /**
     * 缴费
     * @return
     */
    public function edit(Request $request) {
        return (new ProjectPayRepo)->edit($request);
    }

    public function getListByProject($id,Request $request) {
        return (new ProjectPayRepo)->getListByProject($id,$request);
    }

}
