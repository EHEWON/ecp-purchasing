<?php

namespace App\Modules\Admin;

use App\Common\Contracts\Module;
use App\Modules\Admin\Events\AdminLoginLogEvent;

class AdminModule extends Module {

    public function getListen() {
        return [
            AdminLoginLogEvent::class => []
        ];
    }

    public function getSubscribe() {
        
    }

}
