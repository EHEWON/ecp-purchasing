<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class MessageController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('MessageService')->getList($request);
    }

    public function info($id) {
        return Admin::service('MessageService')->info($id);
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
    public function notReadCount(Request $request) {
        return Admin::service('MessageService')->notReadCount($request);
    }

}
