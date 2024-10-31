<?php

namespace App\Modules\Frontend;

use Illuminate\Support\Facades\Facade;
use Spatie\Permission\Traits\HasRoles;

class Frontend extends Facade {

    use HasRoles;

    public static function getFacadeAccessor() {
        return 'App\Modules\Frontend\FrontendModule';
    }

}
