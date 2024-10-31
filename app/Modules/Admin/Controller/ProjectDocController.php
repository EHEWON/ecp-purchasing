<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class ProjectDocController extends Controller {

    public function getRules() {
        return [];
    }
    public function info($id) {
        return Admin::service('ProjectDocService')->info($id);
    } 

    public function edited($id, Request $request) {
        $request->merge(['id' => $id]);
        return Admin::service('ProjectDocService')->pass($request)->runTransaction('edited');
    }

}
