<?php

namespace App\Modules\Frontend\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Frontend\Frontend;
use Illuminate\Http\Request;

class IndexController extends Controller {

    public function getRules() {
        return [];
    }

    public function index() {
        return Frontend::service('IndexService')->index();
    }

    public function getList(Request $request) {
        return Frontend::service('IndexService')->getList($request);
    }

}
