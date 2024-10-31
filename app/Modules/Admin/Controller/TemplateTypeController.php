<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class TemplateTypeController extends Controller {

    public function getRules() {
        return [];
    }

    public function getAll(Request $request) {
        return Admin::service('TemplateTypeService')->getAll($request);
    }

}
