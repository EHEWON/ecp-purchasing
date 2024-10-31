<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class QuoteController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('QuoteService')->getList($request);
    }

    public function info($id) {
        return Admin::service('QuoteService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('QuoteService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('QuoteService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('QuoteService')->pass($request)->runTransaction('enable');
    }

    public function disable(Request $request) {
        return Admin::service('QuoteService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('QuoteService')->pass($request)->runTransaction('delete');
    }

    public function import(Request $request) {
        return Admin::service('QuoteService')->import($request);
    }

    public function export(Request $request) {
        return Admin::service('QuoteService')->export($request);
    }

    public function number() {
        return Admin::service('QuoteService')->number();
    }

    public function sum_quote($inquiry_id) {
        return Admin::service('QuoteService')->sumQuote($inquiry_id);
    }

//    public function details($inquiry_id) {
//        return Admin::service('QuoteService')->details($inquiry_id);
//    }

}
