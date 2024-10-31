<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class InquiryController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('InquiryService')->getList($request);
    }

    public function info($id) {
        return Admin::service('InquiryService')->info($id);
    }

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('InquiryService')->pass($request)->runTransaction('edited');
    }

    public function add(Request $request) {
        return Admin::service('InquiryService')->pass($request)->runTransaction('add');
    }

    public function enable(Request $request) {
        return Admin::service('InquiryService')->pass($request)->runTransaction('enable');
    }

    public function audit(Request $request) {
        return Admin::service('InquiryService')->pass($request)->runTransaction('audit');
    }

    public function disable(Request $request) {
        return Admin::service('InquiryService')->pass($request)->runTransaction('disable');
    }

    public function delete(Request $request) {
        return Admin::service('InquiryService')->delete($request);
    }

    public function import(Request $request) {
        return Admin::service('InquiryService')->import($request);
    }

    public function export(Request $request) {
        return Admin::service('InquiryService')->export($request);
    }

    public function number() {
        return Admin::service('InquiryService')->number();
    }

    public function copy($id) {
        return Admin::service('InquiryService')->copy($id);
    }

    public function change($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('InquiryService')->change($request);
    }

    public function revoke($id) {
        return Admin::service('InquiryService')->revoke($id);
    }

    public function stop($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('InquiryService')->stop($request);
    }

    public function mulRound($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('InquiryService')->pass($request)->runTransaction('mulRound');
    }

    public function openType() {
        return Admin::service('InquiryService')->openType();
    }

    public function billStatus() {
        return Admin::service('InquiryService')->billStatus();
    }

    public function supScope() {
        return Admin::service('InquiryService')->supScope();
    }

    public function taxCalType() {
        return Admin::service('InquiryService')->taxCalType();
    }

    public function bizStatus() {
        return Admin::service('InquiryService')->bizStatus();
    }

    public function opening($id) {
        return Admin::service('InquiryService')->opening($id);
    }

    public function notice($id) {
        return Admin::service('InquiryService')->notice($id);
    }

    public function entryExport($id) {
        return Admin::service('InquiryService')->entryExport($id);
    }

    public function invType() {
        return Admin::service('InquiryService')->invType();
    }

    public function entryImport(Request $request) {
        return Admin::service('InquiryService')->entryImport($request);
    }

    public function entryTemplate(Request $request) {
        return Admin::service('InquiryService')->entryTemplate($request);
    }

    public function supplier($id) {
        return Admin::service('InquiryService')->supplier($id);
    }

    public function mulquote($id) {
        return Admin::service('InquiryService')->mulquote($id);
    }

}
