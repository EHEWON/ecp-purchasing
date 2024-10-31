<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class MenusController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('MenusService')->getList($request);
    }
    public function userTree(Request $request) {
        return Admin::service('MenusService')->userTree($request);
    }
    public function userMenus(Request $request) {
        return Admin::service('MenusService')->userMenus($request);
    }
    public function tree(Request $request) {
        return Admin::service('MenusService')->getTreeList($request);
    }
    public function info($id) {
        return Admin::service('MenusService')->info($id);
    }

    public function edited($id, Request $request) {
        return Admin::service('MenusService')->edited($id, $request);
    }

    public function add(Request $request) {
        return Admin::service('MenusService')->add($request);
    }

    public function enable(Request $request) {
        return Admin::service('MenusService')->enable($request);
    }

    public function disable(Request $request) {
        return Admin::service('MenusService')->disable($request);
    }

    public function delete(Request $request) {
        return Admin::service('MenusService')->delete($request);
    }

}
