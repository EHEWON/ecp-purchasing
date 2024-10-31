<?php

namespace App\Common\Models;

class UserType extends BaseModel {

    protected $table = 'user_type';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
