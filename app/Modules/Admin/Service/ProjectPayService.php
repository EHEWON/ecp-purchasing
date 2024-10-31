<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\Project\ProjectPayRepo;
use Illuminate\Http\Request;

class ProjectPayService extends Service {

    protected $guard = 'admin';
    public $middleware = [ ];
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

    public function getListByProject(int $id,Request $request) {
        return (new ProjectPayRepo)->getListByProject($id,$request);
    }

    /**
     * 缴费信息
     * @return
     */
    public function info($id) {
        return (new ProjectPayRepo)->info($id);
    }

    /**
     * 收款缴费
     * @return
     */
    public function audit(Request $request) {
        return (new ProjectPayRepo)->audit($request->id, $request);
    }
    /**
     * 退款缴费
     * @return
     */
    public function returnAudit(Request $request) {
        return (new ProjectPayRepo)->returnAudit($request->id, $request);
    }

}
