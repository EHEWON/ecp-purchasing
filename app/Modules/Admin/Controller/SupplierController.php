<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class SupplierController extends Controller {

    public function getRules() {
        return [];
    }

    public function register(Request $request) {
        return Admin::service('SupplierService')->pass($request)->runTransaction('register');
    }

    public function manage() {
        return Admin::service('SupplierService')->manage();
    }

    public function getList(Request $request) {
        return Admin::service('SupplierService')->getList($request);
    }

    public function info($id, Request $request) {
        return Admin::service('SupplierService')->info($id, $request);
    }

    public function enterpriseType() {
        return Admin::service('SupplierService')->enterpriseType();
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('SupplierService')->pass($request)->runTransaction('edited');
    }

    /**
     * 发送验证码
     * @param  Request $request 
     * @desc 注册时根据邮箱发送验证码
     * @return 
     */
    public function phoneEmail(Request $request) {
        return Admin::service('SupplierService')
                        ->pass($request)
                        ->runTransaction('phoneEmail');
    }

    /**
     * 发送验证码
     * @param  Request $request 
     * @desc 注册时根据邮箱发送验证码
     * @return 
     */
    public function company(Request $request) {
        return Admin::service('SupplierService')
                        ->pass($request)
                        ->runTransaction('company');
    }

    public function notice(Request $request) {
        return Admin::service('SupplierService')->notice($request);
    }

    public function noticeInfo($notice_id) {
        return Admin::service('SupplierService')->noticeInfo($notice_id);
    }

    public function noticeDelete(Request $request) {
        return Admin::service('SupplierService')->noticeDelete($request);
    }

    public function noticeRead(Request $request) {
        return Admin::service('SupplierService')->noticeRead($request);
    }

    public function noticeUnread(Request $request) {
        return Admin::service('SupplierService')->noticeUnread($request);
    }

    public function defaultContact() {
        return Admin::service('SupplierService')->defaultContact();
    }

}
