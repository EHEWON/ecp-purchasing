<?php

namespace App\Common\Models;

class RoleHasPermissions extends BaseModel {

    protected $table = "role_has_permissions";
    protected $primaryKey = 'id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    //JSON 隐藏
    protected $hidden = [
        'deleted_flag',
    ];
    protected $fillable = [
        'permissions_id', 'role_id', 'scope'
    ];

}
