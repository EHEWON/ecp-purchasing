<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class CurrencyController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('CurrencyService')->getList($request);
    }

    public function currencys(Request $request) {
        return Admin::service('CurrencyService')->currencys($request);
    }

    public function info($id) {
        return Admin::service('CurrencyService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('CurrencyService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('CurrencyService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('CurrencyService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('CurrencyService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('CurrencyService')->pass($request)->runTransaction('delete');
    }

    public function export(Request $request) {
        return Admin::service('CurrencyService')->export($request);
    }

    public function import(Request $request) {
        return Admin::service('CurrencyService')->import($request);
    }

}
