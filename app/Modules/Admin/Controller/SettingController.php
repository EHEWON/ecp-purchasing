<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class SettingController extends Controller {

    public function getRules() {
        return [];
    }

    public function getList(Request $request) {
        return Admin::service('SettingService')->getList($request);
    }

    public function updateOrAdd(Request $request) {
        return Admin::service('SettingService')->pass($request)->runTransaction('updateOrAdd');
    }

    public function delete(Request $request) {
        return Admin::service('SettingService')->pass($request)->runTransaction('delete');
    }

}
