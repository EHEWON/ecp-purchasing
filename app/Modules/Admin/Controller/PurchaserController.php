<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class PurchaserController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('PurchaserService')->getList($request);
    }

    public function getAll(Request $request) {
        return Admin::service('PurchaserService')->getAll($request);
    }

    public function tree(Request $request) {
        return Admin::service('PurchaserService')->tree($request);
    }

    public function info($id) {
        return Admin::service('PurchaserService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('PurchaserService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('PurchaserService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('PurchaserService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('PurchaserService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('PurchaserService')->pass($request)->runTransaction('delete');
    }

    public function export(Request $request) {
        return Admin::service('PurchaserService')->export($request);
    }

    public function import(Request $request) {
        return Admin::service('PurchaserService')->import($request);
    }

    public function number() {
        return Admin::service('OrgService')->number();
    }

    /**
     * 发送验证码
     * @param  Request $request 
     * @desc 注册时根据邮箱发送验证码
     * @return 
     */
    public function phoneEmail(Request $request) {
        return Admin::service('PurchaserService')
                        ->pass($request)
                        ->runTransaction('phoneEmail');
    }

    public function register(Request $request) {
        return Admin::service('PurchaserService')->pass($request)->runTransaction('register');
    }

}
