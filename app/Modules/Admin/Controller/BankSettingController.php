<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class BankSettingController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('BankSettingService')->getList($request);
    }

    public function info($id) {
        return Admin::service('BankSettingService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('BankSettingService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('BankSettingService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('BankSettingService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('BankSettingService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('BankSettingService')->pass($request)->runTransaction('delete');
    }

    public function export(Request $request) {
        return Admin::service('BankSettingService')->export($request);
    }

    public function import(Request $request) {
        return Admin::service('BankSettingService')->pass($request)->runTransaction('import');
    }

    public function number() {
        return Admin::service('BankSettingService')->number();
    }

}
