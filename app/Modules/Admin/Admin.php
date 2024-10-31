<?php

namespace App\Modules\Admin;

use Illuminate\Support\Facades\Facade;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Facade {

    use HasRoles;

    public static function getFacadeAccessor() {
        return 'App\Modules\Admin\AdminModule';
    }

}
