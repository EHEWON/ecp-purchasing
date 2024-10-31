<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class BidModeController extends Controller {

    public function getRules() {
        return [];
    }

    public function getAll() {
        return Admin::service('BidModeService')->getAll();
    }

    public function getList(Request $request) {
        return Admin::service('BidModeService')->getList($request);
    }

}
