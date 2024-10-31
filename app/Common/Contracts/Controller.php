<?php

namespace App\Common\Contracts;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

abstract class Controller extends BaseController {

    protected $lang = 'en';

    public function __construct(Request $request) {
        //验证字段
        $this->lang = app('translator')->getLocale();
        $route = $request->route();
        if ($route) {
            list(, $action) = explode('@', $route[1]['uses']);
            if (isset($route[2])) {
                $request->merge($route[2]);
            }

            $rules = $this->getRules();
            if (isset($rules[$action])) {
                $this->validate($request, $rules[$action], [], []);
            }
        }
    }

    abstract public function getRules();
}
