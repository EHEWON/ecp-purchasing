<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class SupplierProjectPayController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('SupplierProjectPayService')->getList($request);
    }

    public function info($id) {
        return Admin::service('SupplierProjectPayService')->info($id);
    }

    public function edit(Request $request) {
        return Admin::service('SupplierProjectPayService')->edit($request);
    }
    public function getListByProject(int $id,Request $request) {
        return Admin::service('SupplierProjectPayService')->getListByProject($id,$request);
    }
    
}
