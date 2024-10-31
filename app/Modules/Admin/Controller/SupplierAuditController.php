<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class SupplierAuditController extends Controller {

    public function getRules() {
        return [];
    }

    /**
     * 列表页
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pending(Request $request) {
        return Admin::service('SupplierAuditService')->pending($request);
    }

    /**
     * 审核历史
     */
    public function history(Request $request) {
        return Admin::service('SupplierAuditService')->history($request);
    }

    /**
     * 审核历史
     */
    public function comments(Request $request) {
        return Admin::service('SupplierAuditService')->comments($request);
    }

    /**
     * 审核状态
     */
    public function status() {
        return Admin::service('SupplierAuditService')->status();
    }

    /**
     * 审核状态
     */
    public function todo() {
        return Admin::service('SupplierAuditService')->todo();
    }

    /**
     * 导出字段
     * @param  Request $request 
     * @return 
     */
    public function verify(Request $request) {
        return Admin::service('SupplierAuditService')->pass($request->post())->runTransaction('verify');
    }

    /**
     * 导出字段
     * @param  Request $request 
     * @return 
     */
    public function audit(Request $request) {
        return Admin::service('SupplierAuditService')->pass($request->post())->runTransaction('audit');
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function getList(Request $request) {
        return Admin::service('SupplierAuditService')->getList($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function progress() {
        return Admin::service('SupplierAuditService')->progress();
    }

}
