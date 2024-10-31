<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class UserController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('UserService')->getList($request);
    }

    public function info($id) {
        return Admin::service('UserService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('UserService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('UserService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('UserService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('UserService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('UserService')->pass($request)->runTransaction('delete');
    }

    public function export(Request $request) {
        return Admin::service('UserService')->export($request);
    }

//
//    public function import(Request $request) {
//        return Admin::service('UserService')->import($request);
//    }

    public function pinyin(Request $request) {
        return Admin::service('UserService')->pinyin($request);
    }

    public function roles(Request $request) {
        return Admin::service('UserService')->roles($request);
    }
    public function getUserByRole(Request $request) {
        return Admin::service('UserService')->getUserByRole($request);
    }

    public function menus(Request $request) {
        return Admin::service('UserService')->menus($request);
    }

    public function orgs(Request $request) {
        return Admin::service('UserService')->orgs($request);
    }

    public function orgTree(Request $request) {
        return Admin::service('UserService')->orgTree($request);
    }

    public function orgList(Request $request) {
        return Admin::service('UserService')->orgList($request);
    }

    /**
     * 重置密码
     * @return 
     */
    public function change(Request $request) {
        return Admin::service('UserService')->pass($request->post())->run('change');
    }

    /**
     * 获取业务员
     * @return 
     */
    public function persons(Request $request) {
        return Admin::service('UserService')->persons($request);
    }

}
