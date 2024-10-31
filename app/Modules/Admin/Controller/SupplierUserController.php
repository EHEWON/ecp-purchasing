<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;
use App\Common\Models\UserSupplier;
use Illuminate\Support\Facades\Auth;

class SupplierUserController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('SupplierUserService')->getList($request);
    }

    public function info($id) {
        return Admin::service('SupplierUserService')->info($id);
    }

    public function edited($id, Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $supplier = UserSupplier::where('user_id', $admin->user_id)
          ->where('deleted_flag', 'N')
          ->first();
        if (empty($supplier)) {
            check(false, '您不是供应商用户或您的账号没有绑定供应商');
        }
        $request->merge(['id' => $id, 'user_type' => 'SUPPLIER', 'user_supplier' => $supplier->toArray()]);
        return Admin::service('SupplierUserService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $supplier = UserSupplier::where('user_id', $admin->user_id)
          ->where('deleted_flag', 'N')
          ->first();
        if (empty($supplier)) {
            check(false, '您不是供应商用户或您的账号没有绑定供应商');
        }
        $request->merge(['user_type' => 'SUPPLIER', 'user_supplier' => $supplier->toArray()]);
        return Admin::service('SupplierUserService')
            ->pass($request)
            ->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('SupplierUserService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('SupplierUserService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('SupplierUserService')->pass($request)->runTransaction('delete');
    }

    public function export(Request $request) {
        return Admin::service('SupplierUserService')->export($request);
    }

    public function pinyin(Request $request) {
        return Admin::service('SupplierUserService')->pinyin($request);
    }

    /**
     * 重置密码
     * @return 
     */
    public function change(Request $request) {
        return Admin::service('SupplierUserService')->pass($request->post())->run('change');
    }

    public function roles(Request $request) {
        return Admin::service('SupplierUserService')->roles($request);
    }

    public function menus(Request $request) {
        return Admin::service('SupplierUserService')->menus($request);
    }

    public function rolesuser(Request $request) {
        return Admin::service('SupplierUserService')->pass($request)->runTransaction('rolesuser');
    }

}
