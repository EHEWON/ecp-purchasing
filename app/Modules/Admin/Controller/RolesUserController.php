<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class RolesUserController extends Controller {

    public function getRules() {
        return [];
    }

    public function updateOrAdd(Request $request) {
        return Admin::service('RolesUserService')->pass($request)->runTransaction('updateOrAdd');
    }

    public function delete(Request $request) {
        return Admin::service('RolesUserService')->pass($request)->runTransaction('delete');
    }

}
