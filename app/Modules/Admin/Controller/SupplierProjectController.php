<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SupplierProjectController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('SupplierProjectService')->getList($request);
    }

    public function info($id) {
        return Admin::service('SupplierProjectService')->info($id);
    }

    public function noticeInfo($id) {
        return Admin::service('SupplierProjectService')->noticeInfo($id);
    }

    public function cmfInfo($id) {
        return Admin::service('SupplierProjectService')->cmfInfo($id);
    }

    public function signUp(int $id, Request $request) {
        DB::beginTransaction();
        $response = Admin::service('SupplierProjectService')->signUp($id, $request);
        DB::commit();
        return $response;
    }

    public function unSignUp(int $id, Request $request) {
        DB::beginTransaction();
        $response = Admin::service('SupplierProjectService')->unSignUp($id, $request);
        DB::commit();
        return $response;
    }

    public function quote(int $id, Request $request) {
        DB::beginTransaction();
        $response = Admin::service('SupplierProjectService')->quote($id, $request);
        DB::commit();
        return $response;
    }

    public function quoteedited(int $id, Request $request) {
        DB::beginTransaction();
        $response = Admin::service('SupplierProjectService')->quoteedited($id, $request);
        DB::commit();
        return $response;
    }

    public function quoteinfo($id) {
        return Admin::service('SupplierProjectService')->quoteinfo($id);
    }

    public function publishDownload($id) {
        return Admin::service('SupplierProjectService')->publishDownload($id);
    }

    public function docDownload(string $group, $id, Request $request) {
        return Admin::service('SupplierProjectService')->docDownload($group, $id, $request);
    }

    public function download(string $group, $id, Request $request) {
        return Admin::service('SupplierProjectService')->download($group, $id,  $request);
    }

}
