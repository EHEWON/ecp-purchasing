<?php

namespace App\Common\Models;

class RoleHasMenus extends BaseModel {

    protected $table = "role_has_menus";
    protected $primaryKey = 'id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    //JSON 隐藏
    protected $hidden = [
        'deleted_flag',
    ];
    protected $fillable = [
        'menus_id', 'role_id', 'scope'
    ];

}
