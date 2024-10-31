<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class TemplateController extends Controller {

    public function getRules() {
        return [];
    }

    public function getAll(Request $request) {
        return Admin::service('TemplateService')->getAll($request);
    }

    public function getList(Request $request) {
        return Admin::service('TemplateService')->getList($request);
    }

    public function info($id) {
        return Admin::service('TemplateService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('TemplateService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('TemplateService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('TemplateService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('TemplateService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('TemplateService')->pass($request)->runTransaction('delete');
    }

    public function number() {
        return Admin::service('TemplateService')->number();
    }

}
