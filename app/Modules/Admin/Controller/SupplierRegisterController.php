<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class SupplierRegisterController extends Controller {

    public function getRules() {
        return [];
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function getList(Request $request) {
        return Admin::service('SupplierRegisterService')->getList($request);
    }

}
