<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class DivisionController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('DivisionService')->getList($request);
    }

    public function tree(Request $request) {
        return Admin::service('DivisionService')->tree($request);
    }

    public function china(Request $request) {
        return Admin::service('DivisionService')->china($request);
    }

    public function info($id) {
        return Admin::service('DivisionService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('DivisionService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('DivisionService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('DivisionService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('DivisionService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('DivisionService')->pass($request)->runTransaction('delete');
    }

    public function export(Request $request) {
        return Admin::service('DivisionService')->export($request);
    }

    public function import(Request $request) {
        return Admin::service('DivisionService')->import($request);
    }

    public function number() {
        return Admin::service('DivisionService')->number();
    }

}
