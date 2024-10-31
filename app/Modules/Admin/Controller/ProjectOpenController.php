<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class ProjectOpenController extends Controller {

    public function getRules() {
        return [];
    }
    public function info($id) {
        return Admin::service('ProjectOpenService')->info($id);
    } 

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('ProjectOpenService')->pass($request)->runTransaction('edited');
    }

}
