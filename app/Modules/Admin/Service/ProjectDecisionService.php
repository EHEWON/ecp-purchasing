<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\Project\ProjectDecisionRepo;
use Illuminate\Http\Request;

class ProjectDecisionService extends Service {

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
     * 缴费信息
     * @return
     */
    public function getList(Request $request) {
        return (new ProjectDecisionRepo)->getList($request);
    }

    /**
     * 缴费信息
     * @return
     */
    public function info($id) {
        return (new ProjectDecisionRepo)->info($id);
    }

    /**
     * 缴费信息
     * @return
     */
    public function edited(Request $request) {
        return (new ProjectDecisionRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new ProjectDecisionRepo)->deleteData($request);
    }

}
