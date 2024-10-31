<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class PurProjectController extends Controller {

    public function getRules() {
        return [];
    }

    public function getAll() {
        return Admin::service('PurProjectService')->getAll();
    }

    public function getList(Request $request) {
        return Admin::service('PurProjectService')->getList($request);
    }

}
