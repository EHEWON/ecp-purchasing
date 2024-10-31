<?php

namespace App\Common\Models;

class Bank extends BaseModel {

    protected $table = 'bank';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
