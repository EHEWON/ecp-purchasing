<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use Illuminate\Http\Request;
use App\Modules\Admin\Admin;

class IndexController extends Controller {

    public function getRules() {
        return [];
    }

    public function statistics() {
        return Admin::service('IndexService')->statistics();
    }

    public function todo() {
        return Admin::service('IndexService')->todo();
    }

    public function inquiryStatistics(Request $request) {
        return Admin::service('IndexService')->inquiryStatistics($request);
    }

    public function quoteStatistics(Request $request) {
        return Admin::service('IndexService')->quoteStatistics($request);
    }

    public function quickMenus() {
        return Admin::service('IndexService')->quickMenus();
    }

}
