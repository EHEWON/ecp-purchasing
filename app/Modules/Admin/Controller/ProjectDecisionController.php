<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class ProjectDecisionController extends Controller {

    public function getRules() {
        return [];
    }

    public function info($id) {
        return Admin::service('ProjectDecisionService')->info($id);
    }

    public function getList(Request $request) {
        return Admin::service('ProjectDecisionService')->getList($request);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('ProjectDecisionService')->pass($request)->runTransaction('edited');
    }

    public function delete(Request $request) {
        return Admin::service('ProjectDecisionService')->pass($request)->runTransaction('delete');
    }

}
