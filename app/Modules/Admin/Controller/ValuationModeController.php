<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class ValuationModeController extends Controller {

    public function getRules() {
        return [];
    }

    public function getAll() {
        return Admin::service('ValuationModeService')->getAll();
    }

    public function getList(Request $request) {
        return Admin::service('ValuationModeService')->getList($request);
    }

}
