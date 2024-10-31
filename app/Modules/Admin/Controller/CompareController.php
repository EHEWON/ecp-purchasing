<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class CompareController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('CompareService')->getList($request);
    }

    public function info($id) {
        return Admin::service('CompareService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('CompareService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('CompareService')->pass($request)->runTransaction('add');
    }
    public function export(Request $request) {
        return Admin::service('CompareService')->export($request);
    }
    public function delete(Request $request) {
        return Admin::service('CompareService')->pass($request)->runTransaction('delete');
    }

    public function number() {
        return Admin::service('CompareService')->number();
    }
    public function verify($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('CompareService')->pass($request)->runTransaction('verify');
    }
     public function stop($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('CompareService')->pass($request)->runTransaction('stop');
    }

    public function notice(Request $request) {
        return Admin::service('CompareService')->notice($request);
    }
    
    public function getListGroupBySupplier(Request $request) {
        return Admin::service('CompareService')->getListGroupBySupplier($request);
    }

}
