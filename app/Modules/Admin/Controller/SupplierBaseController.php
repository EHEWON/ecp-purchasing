<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class SupplierBaseController extends Controller {

    public function getRules() {
        return [];
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function getList(Request $request) {
        return Admin::service('SupplierBaseService')->getList($request);
    }

    public function suppliers(Request $request) {
        return Admin::service('SupplierBaseService')->suppliers($request);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id, 'status' => 'APPROVING']);
        return Admin::service('SupplierBaseService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        $request->merge(['status' => 'APPROVING']);
        return Admin::service('SupplierBaseService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('SupplierBaseService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('SupplierBaseService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('SupplierBaseService')->delete($request);
    }

    public function export(Request $request) {
        return Admin::service('SupplierBaseService')->export($request);
    }

    public function import(Request $request) {
        return Admin::service('SupplierBaseService')->import($request);
    }

    public function status() {
        return Admin::service('SupplierBaseService')->status();
    }

    /**
     * 人员类型列表
     * @param int $id
     */
    public function auditInfo(int $id, Request $request) {
        return Admin::service('SupplierBaseService')->auditInfo($id, $request);
    }

    /**
     * 人员类型列表
     * @param int $id
     */
    public function editInfo(int $id, $request) {
        return Admin::service('SupplierBaseService')->editInfo($id, $request);
    }

    public function info($id, Request $request) {
        return Admin::service('SupplierBaseService')->info($id, $request);
    }

    public function formal(Request $request) {
        return Admin::service('SupplierBaseService')->formal($request);
    }

    public function template() {
        return env('SUPPLIER_TEMP');
    }

}
