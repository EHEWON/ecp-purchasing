<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class NoticeController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('NoticeService')->getList($request);
    }

    public function info($id) {
        return Admin::service('NoticeService')->info($id);
    }

    public function delete(Request $request) {
        return Admin::service('MessageService')->delete($request);
    }

    public function read(Request $request) {
        return Admin::service('MessageService')->read($request);
    }

    public function unread(Request $request) {
        return Admin::service('MessageService')->unread($request);
    }

}
