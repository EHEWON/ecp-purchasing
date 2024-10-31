<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class SupplierQuoteController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('SupplierQuoteService')->getList($request);
    }

    public function export(Request $request) {
        return Admin::service('SupplierQuoteService')->export($request);
    }

    public function info($id) {
        return Admin::service('SupplierQuoteService')->info($id);
    }

    public function infoByInquiryId($inquiry_id) {
        return Admin::service('SupplierQuoteService')->infoByInquiryId($inquiry_id);
    }

    public function listByInquiryId($inquiry_id) {
        return Admin::service('SupplierQuoteService')->listByInquiryId($inquiry_id);
    }

    public function add(Request $request) {
        return Admin::service('SupplierQuoteService')->pass($request)->runTransaction('add');
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('SupplierQuoteService')->pass($request)->runTransaction('edited');
    }

    public function delete(Request $request) {
        return Admin::service('SupplierQuoteService')->pass($request)->delete($request);
    }

    public function entryExport($id, Request $request) {
        return Admin::service('SupplierQuoteService')->entryExport($id, $request);
    }

    public function entryImport(Request $request) {
        return Admin::service('SupplierQuoteService')->entryImport($request);
    }

}
