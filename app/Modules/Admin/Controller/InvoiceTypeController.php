<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class InvoiceTypeController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('InvoiceTypeService')->getList($request);
    }

    public function info($id) {
        return Admin::service('InvoiceTypeService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('InvoiceTypeService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('InvoiceTypeService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('InvoiceTypeService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('InvoiceTypeService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('InvoiceTypeService')->pass($request)->runTransaction('delete');
    }

    public function import(Request $request) {
        return Admin::service('InvoiceTypeService')->import($request);
    }

    public function export(Request $request) {
        return Admin::service('InvoiceTypeService')->export($request);
    }

    public function number() {
        return Admin::service('InvoiceTypeService')->number();
    }

}
