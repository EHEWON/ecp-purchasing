<?php

/**
 * Created by PhpStorm.

 * Date: 2024/1/24
 * Time: 08:44
 */

namespace App\Common\Models;

// implements AuthenticatableContract, AuthorizableContract
class AdminMenus extends BaseModel {

//    use Authenticatable, Authorizable;

    protected $table = "admin_menus";
    protected $primaryKey = 'menu_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    //JSON 隐藏
    protected $hidden = [
        'deleted_flag',
    ];
    protected $fillable = [
        'menu_id'
    ];

}
