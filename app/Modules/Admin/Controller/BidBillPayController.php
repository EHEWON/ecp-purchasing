<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class BidBillPayController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('BidBillPayService')->getList($request);
    }

    public function info(int $id) {
        return Admin::service('BidBillPayService')->info($id);
    }

    public function payAudit(int $id, Request $request) {
        return Admin::service('BidBillPayService')->payAudit($id, $request);
    }

    public function returnAudit(int $id, Request $request) {
        return Admin::service('BidBillPayService')->returnAudit($id, $request);
    }

}
