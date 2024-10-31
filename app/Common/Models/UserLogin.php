<?php

namespace App\Common\Models;

class UserLogin extends BaseModel {

    protected $table = 'user_login';
    protected $primaryKey = 'id';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
