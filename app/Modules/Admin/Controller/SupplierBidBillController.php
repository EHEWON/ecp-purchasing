<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class SupplierBidBillController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('SupplierBidBillService')->getList($request);
    }

    public function info($id) {
        return Admin::service('SupplierBidBillService')->info($id);
    }

    public function pays(Request $request) {
        return Admin::service('SupplierBidBillService')->pays($request);
    }

    public function pay(int $id, Request $request) {
        return Admin::service('SupplierBidBillService')->pay($id, $request);
    }

    public function payInfo(int $id, Request $request) {
        return Admin::service('SupplierBidBillService')->payInfo($id, $request);
    }

    public function hall(Request $request) {
        return Admin::service('SupplierBidBillService')->hall($request);
    }

    public function hallInfo(int $id) {
        return Admin::service('SupplierBidBillService')->hallInfo($id);
    }

    public function quote(int $id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('SupplierBidBillService')->pass($request)->runTransaction('quote');
    }

    public function bindUid(int $id, Request $request) {
        return Admin::service('SupplierBidBillService')->bindUid($id, $request);
    }

    public function bindGroup(int $id, Request $request) {
        return Admin::service('SupplierBidBillService')->bindGroup($id, $request);
    }

    public function offline(int $id, Request $request) {
        return Admin::service('SupplierBidBillService')->offline($id, $request);
    }

    public function signUp(int $id, Request $request) {
        return Admin::service('SupplierBidBillService')->signUp($id, $request);
    }

    public function unSignUp(int $id, Request $request) {
        return Admin::service('SupplierBidBillService')->unSignUp($id, $request);
    }

}
