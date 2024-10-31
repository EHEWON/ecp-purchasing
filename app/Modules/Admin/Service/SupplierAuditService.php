<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\SupplierAuditRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class SupplierAuditService extends Service {

    protected $guard = 'admin';
    public $middleware = [];
    public $beforeEvent = [];
    public $afterEvent = [
    ];

    public function getRules() {
        return [
            'verify' => [
                'id' => 'required',
                'status' => 'required|in:REJECTED,PASS',
                'remark' => 'required_if:status,REJECTED',
            ],
            'audit' => [
                'id' => 'required',
                'status' => 'required|in:REJECTED,PASS',
                'remark' => 'required_if:status,REJECTED',
            ]
        ];
    }

    public function getMessages() {
        return [
            'verify' => [
                'id.required' => Lang::get('supplier_audit.error_id_required'),
                'status.required' => Lang::get('supplier_audit.error_status_required'),
                'status.in' => Lang::get('supplier_audit.error_status_in'),
                'remark.required_if' => Lang::get('supplier_audit.error_remark_required_if'),
            ],
            'audit' => [
                'id.required' => Lang::get('supplier.message_supplier_id_required'),
                'status.required' => Lang::get('supplier_audit.error_status_required'),
                'status.in' => Lang::get('supplier_audit.error_status_in'),
                'remark.required_if' => Lang::get('supplier_audit.error_remark_required_if'),
            ]
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
    public function audit(Request $request) {
        return (new SupplierAuditRepo)->audit($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function verify(Request $request) {
        return (new SupplierAuditRepo)->verify($request);
    }

    /**
     * 历史审核记录
     * @param Request $request
     */
    public function history(Request $request) {
        return (new SupplierAuditRepo)->history($request);
    }

    /**
     * 审核记录
     * @param Request $request
     */
    public function comments(Request $request) {
        return (new SupplierAuditRepo)->comments($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function status() {
        return (new SupplierAuditRepo)->status();
    }

    /**
     * 人员类型信息
     * @return
     */
    public function todo() {
        return (new SupplierAuditRepo)->todoCount();
    }

    /**
     * 新增人员类型
     * @return
     */
    public function pending(Request $request) {
        return (new SupplierAuditRepo)->pending($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function progress() {
        return (new SupplierAuditRepo)->progress();
    }

}
