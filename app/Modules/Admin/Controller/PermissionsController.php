<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class PermissionsController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('PermissionsService')->getList($request);
    }
    public function tree(Request $request) {
        return Admin::service('PermissionsService')->getTreeList($request);
    }
    public function info($id) {
        return Admin::service('PermissionsService')->info($id);
    }

    public function edited($id, Request $request) {
        return Admin::service('PermissionsService')->edited($id, $request);
    }

    public function add(Request $request) {
        return Admin::service('PermissionsService')->add($request);
    }

    public function enable(Request $request) {
        return Admin::service('PermissionsService')->enable($request);
    }

    public function disable(Request $request) {
        return Admin::service('PermissionsService')->disable($request);
    }

    public function delete(Request $request) {
        return Admin::service('PermissionsService')->delete($request);
    }

}
