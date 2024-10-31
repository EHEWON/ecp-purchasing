<?php

namespace App\Common\Contracts;

abstract class Channel {

    abstract public function handle($request);

    public function register($request) {
        
    }

    public function getCash($request) {
        
    }

    public function callback($request) {
        
    }

}
