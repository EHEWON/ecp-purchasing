<?php

namespace App\Common\Contracts;

use Closure;

abstract class Middleware {

    protected $lang = 'en';

    public function __construct() {
        $this->lang = app('translator')->getLocale();
    }

    abstract public function handle($request, Closure $next);
}
