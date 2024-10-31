<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class ProjectPayController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('ProjectPayService')->getList($request);
    }

    public function info($id) {
        return Admin::service('ProjectPayService')->info($id);
    }
    
    public function getListByProject(int $id,Request $request) {
        return Admin::service('ProjectPayService')->getListByProject($id,$request);
    }

    public function audit($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('ProjectPayService')->pass($request)->runTransaction('audit');
    }

    public function returnAudit($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('ProjectPayService')->pass($request)->runTransaction('returnAudit');
    }

}
