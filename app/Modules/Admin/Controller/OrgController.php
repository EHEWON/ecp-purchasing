<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class OrgController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('OrgService')->getList($request);
    }

    public function getAll(Request $request) {
        return Admin::service('OrgService')->getAll($request);
    }

    public function tree(Request $request) {
        return Admin::service('OrgService')->tree($request);
    }

    public function info($id) {
        return Admin::service('OrgService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('OrgService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('OrgService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('OrgService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('OrgService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('OrgService')->pass($request)->runTransaction('delete');
    }

    public function export(Request $request) {
        return Admin::service('OrgService')->export($request);
    }

    public function import(Request $request) {
        return Admin::service('OrgService')->import($request);
    }

    public function number() {
        return Admin::service('OrgService')->number();
    }

}
