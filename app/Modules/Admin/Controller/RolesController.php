<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class RolesController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('RolesService')->getList($request);
    }

    public function getRoleByCompany(Request $request) {
        return Admin::service('RolesService')->getRoleByCompany($request);
    }

    public function info($id) {
        return Admin::service('RolesService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('RolesService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('RolesService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('RolesService')->enable($request);
    }

    public function disable(Request $request) {
        return Admin::service('RolesService')->disable($request);
    }

    public function delete(Request $request) {
        return Admin::service('RolesService')->delete($request);
    }

    public function hasMenus($id, Request $request) {
        return Admin::service('RolesService')->hasMenus($id, $request);
    }

    public function userHasRoles($id, Request $request) {
        return Admin::service('RolesService')->userHasRoles($id, $request);
    }

    public function menusList($id) {
        return Admin::service('RolesService')->menusList($id);
    }

    public function listbyuser($id, Request $request) {
        return Admin::service('RolesService')->listbyuser($id, $request);
    }

}
