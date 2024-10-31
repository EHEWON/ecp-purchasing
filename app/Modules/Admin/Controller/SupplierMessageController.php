<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class SupplierMessageController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('SupplierMessageService')->getList($request);
    }

    public function info($id) {
        return Admin::service('SupplierMessageService')->info($id);
    }

    public function delete(Request $request) {
        return Admin::service('SupplierMessageService')->delete($request);
    }

    public function read(Request $request) {
        return Admin::service('SupplierMessageService')->read($request);
    }

    public function unread(Request $request) {
        return Admin::service('SupplierMessageService')->unread($request);
    }
    
    public function notReadCount(Request $request) {
        return Admin::service('SupplierMessageService')->notReadCount($request);
    }

}
