<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class NoticeManageController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('NoticeManageService')->getList($request);
    }

    public function info($id) {
        return Admin::service('NoticeManageService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('NoticeManageService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('NoticeManageService')->pass($request)->runTransaction('add');
    }

    public function topping(Request $request) {
        return Admin::service('NoticeManageService')->pass($request)->runTransaction('topping');
    }

    public function cancel(Request $request) {
        return Admin::service('NoticeManageService')->pass($request)->runTransaction('cancel');
    }

    public function delete(Request $request) {
        return Admin::service('NoticeManageService')->pass($request)->runTransaction('delete');
    }

    public function export(Request $request) {
        return Admin::service('NoticeManageService')->export($request);
    }

    public function number() {
        return Admin::service('NoticeManageService')->number();
    }

    public function audit(Request $request) {
        return Admin::service('NoticeManageService')->audit($request);
    }

}
