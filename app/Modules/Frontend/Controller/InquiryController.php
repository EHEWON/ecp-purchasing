<?php

namespace App\Modules\Frontend\Controller;

use App\Common\Contracts\Controller;
use Illuminate\Http\Request;
use App\Modules\Frontend\Frontend;

class InquiryController extends Controller {

    public function getRules() {
        return [];
    }

    public function index(Request $request) {
        return Frontend::service('InquiryService')->index($request);
    }

    public function info(int $id) {
        return Frontend::service('InquiryService')->info($id);
    }

}
