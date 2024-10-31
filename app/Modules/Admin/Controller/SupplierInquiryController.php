<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class SupplierInquiryController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('SupplierInquiryService')->getList($request);
    }

    public function info($id) {
        return Admin::service('SupplierInquiryService')->info($id);
    }

    /**
     * 不报价
     * @return
     */
    public function quote(int $id) {
        return Admin::service('SupplierInquiryService')->quote($id);
    }

    /**
     * 不报价
     * @return
     */
    public function export(Request $request) {
        return Admin::service('SupplierInquiryService')->export($request);
    }

    /**
     * 报价
     * @return
     */
    public function unQuote(int $id) {
        return Admin::service('SupplierInquiryService')->unQuote($id);
    }

}
