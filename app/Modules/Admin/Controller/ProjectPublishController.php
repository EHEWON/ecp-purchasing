<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class ProjectPublishController extends Controller {

    public function getRules() {
        return [];
    }
    public function info($id) {
        return Admin::service('ProjectPublishService')->info($id);
    } 

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('ProjectPublishService')->pass($request)->runTransaction('edited');
    }

}
