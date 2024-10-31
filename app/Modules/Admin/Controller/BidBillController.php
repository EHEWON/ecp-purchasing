<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class BidBillController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('BidBillService')->getList($request);
    }

    public function info($id) {
        return Admin::service('BidBillService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('BidBillService')->pass($request)->runTransaction('add');
    }

    public function delete(Request $request) {
        return Admin::service('BidBillService')->delete($request);
    }

    public function change($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('change');
    }

    public function number() {
        return Admin::service('BidBillService')->number();
    }

    public function export(Request $request) {
        return Admin::service('BidBillService')->export($request);
    }

    public function hall(Request $request) {
        return Admin::service('BidBillService')->hall($request);
    }

    public function decision_list(Request $request) {
        return Admin::service('BidBillService')->decision_list($request);
    }

    public function check(int $id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('check');
    }

    public function pay(int $id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('pay');
    }

    public function returns(int $id) {
        return Admin::service('BidBillService')->returns($id);
    }

    public function start(int $id) {
        $request = new Request();
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('start');
    }

    public function suppliers(int $id) {
        return Admin::service('BidBillService')->suppliers($id);
    }

    public function pays(int $id, Request $request) {
        return Admin::service('BidBillService')->pays($id, $request);
    }

    public function returnDeposit(int $id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('returnDeposit');
    }

    public function supplierPay(int $id, Request $request) {
        return Admin::service('BidBillService')->supplierPay($id, $request);
    }

    public function stop(int $id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('stop');
    }

    public function decision(int $id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('decision');
    }

    public function begin(int $id) {
        $request = new Request();
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('begin');
    }

    public function winning(int $id) {
        return Admin::service('BidBillService')->winning($id);
    }

    public function termination(int $id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('termination');
    }

    public function finished(int $id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('BidBillService')->pass($request)->runTransaction('finished');
    }

    public function hallInfo(int $id) {
        return Admin::service('BidBillService')->hallInfo($id);
    }

    public function bindUid(int $id, Request $request) {
        return Admin::service('BidBillService')->bindUid($id, $request);
    }

    public function bindGroup(int $id, Request $request) {
        return Admin::service('BidBillService')->bindGroup($id, $request);
    }

    public function offline(int $id, Request $request) {
        return Admin::service('BidBillService')->offline($id, $request);
    }

    public function entryExport(int $id) {
        return Admin::service('BidBillService')->entryExport($id);
    }

    public function entryImport(Request $request) {
        return Admin::service('BidBillService')->entryImport($request);
    }

    public function entryTemplate(Request $request) {
        return Admin::service('BidBillService')->entryTemplate($request);
    }

}
