<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class ProjectController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('ProjectService')->getList($request);
    }

    public function info($id) {
        return Admin::service('ProjectService')->info($id);
    }

    public function quoteInfo($id) {
        return Admin::service('ProjectService')->quoteInfo($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('ProjectService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('ProjectService')->pass($request)->runTransaction('add');
    }

    public function delete(Request $request) {
        return Admin::service('ProjectService')->delete($request);
    }

    public function invalid(Request $request) {
        return Admin::service('ProjectService')->invalid($request);
    }

    public function invalidInfo($id) {
        return Admin::service('ProjectService')->invalidInfo($id);
    }

    public function export(Request $request) {
        return Admin::service('ProjectService')->export($request);
    }

    public function entryImport(Request $request) {
        return Admin::service('ProjectService')->entryImport($request);
    }

    public function entryTemplate(Request $request) {
        return Admin::service('ProjectService')->entryTemplate($request);
    }

    public function change($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('ProjectService')->pass($request)->runTransaction('change');
    }

    public function suppliers(Request $request) {
        return Admin::service('ProjectService')->suppliers($request);
    }

    public function members(Request $request) {
        return Admin::service('ProjectService')->members($request);
    }

    public function decision(Request $request) {
        return Admin::service('ProjectService')->decision($request);
    }

    public function shortlist($id, Request $request) {
        return Admin::service('ProjectService')->shortlist($id, $request);
    }

    public function shortlistaddData($id, Request $request) {
        return Admin::service('ProjectService')->shortlistaddData($id, $request);
    }

    public function number() {
        return Admin::service('ProjectService')->number();
    }

    public function supplierList(Request $request) {
        return Admin::service('ProjectService')->supplierList($request);
    }

    public function download(string $group, int $quote_id) {
        return Admin::service('ProjectService')->download($quote_id, $group);
    }

}
