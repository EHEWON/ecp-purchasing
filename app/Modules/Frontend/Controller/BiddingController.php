<?php

namespace App\Modules\Frontend\Controller;

use App\Common\Contracts\Controller;
use Illuminate\Http\Request;
use App\Modules\Frontend\Frontend;

class BiddingController extends Controller {

    public function getRules() {
        return [];
    }

    public function index(Request $request) {
        return Frontend::service('BiddingService')->index($request);
    }

    public function info(int $id) {
        return Frontend::service('BiddingService')->info($id);
    }

}
